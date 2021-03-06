#!/usr/bin/env python3

# coded by bruno, 2019

# all in all, I consume a "specs" file and generates testbenches based on good
# submissions. it uses tricks such as not doing tests which good submissions
# give different results to, for instance.

# basically, the specs file gives me the following information:
#   - names of inputs and outputs
#   - specs for inputs (size in bits, randomize, combinations, etcetera)
#   - lag time between reads
#   - paths to "good" submissions

# the following is expected for context (folder I am run in)
#   - Makefile (just like in a regular testbench) that prepares auxiliar files
#   - said auxiliar files
#   - skeleton.vhd file, basically, a regular testbench, but with the "inputs"
#     and "asserts" replaced by a comment that reads "-- SKELETON".
#   - gjvnq's "utils.vhd" library, current copy is here
# mind you, an example skeleton a json specs file are supplied in this folder.

# I will do the following:
#   - first, I will use the "skeleton.vhd" file and the input specs to create
#     a "preliminary testbench" file, tb.vhd. that testbench asserts nothing,
#     it just sends the inputs and reports the outputs.
#   - then, for every "good" submission known, I will compile it against the
#     preliminary testbench, run it, and store the output for every input.
#   - finally, I will compare the results, remove any inputs for which outputs
#     differ between the "good" submissions, and generate a "final testbench",
#     with assertions and all. it's up to you to zip it.

# beware, nothing here is firejailed. be wary of the submissions you choose to
# run, or firejail me as a whole!

# some special strings
BAD = "ghdlfiddle:BAD"
GOOD = "ghdlfiddle:GOOD"
DEBUG = "ghdlfiddle:DEBUG"
FINISHED = GOOD + " -- Testbench finished executing."
TB = "tb.vhd"
TARGET = "target.vhd"
SKELETON = "skeleton.vhd"
SKEL_LINE = "-- SKELETON HERE"
PREPARE = "make setup >/dev/null"
ANALYSE = "ghdl -a %s; ghdl -a %s" % (TARGET, TB)
ELABORATE = "ghdl -e tb"
RUN = "ghdl -r tb 2>&1"
CLEANUP = "rm -f *.o *.cf target.vhd tb"


# now, import some stuff!
import sys, os, re, json, random
from itertools import product


# auxiliary function to generate a random bitstring of length n
def bitstring(n):
  b = ""
  while len(b) != n:
    b += str(random.randint(0, 1))
  return b

# auxiliary function to represent binary values in VHDL
def vhdl_binary(bits):
  return ("'%s'" if len(bits) == 1 else "\"%s\"") % (bits,)

# auxiliary function to wait
def vhdl_wait(delay):
  return "wait for %s;\n" % (delay,)

# auxiliary function to turn an input dict into a wait-guarded series of
# assignments, and asserts their values if a dict of outputs is given
def vhdl_assign(ins, delay, outs_names, expects=None):
  code = vhdl_wait(delay)
  for name, bits in ins.items():
    code += name + " <= " + vhdl_binary(bits) + ";\n"
  code += vhdl_wait(delay)
  outs_report = "{" + ",".join(["'%s': '\"&bin(%s)&\"'" % (name, name)
                         for name in outs_names]) + "}";
  if expects:
    # if a list of expectations is given, I'll create asserts, for we are
    # making the final testbench
    for name, bits in expects.items():
      code += "assert (%s = %s)\nreport " % (name, vhdl_binary(bits))
      code += (("\"  -- %s --\\n    · with inputs: %s\\n    · with outputs: %s"
               + "\\n    · expected %s to "
               + "be %s, got %s!\\n\";\n")
               % (BAD, str(ins), outs_report, name, bits,
                  "\"&bin(" + name + ")&\""))
  else:
    # if a list of expectations is not given, I'll merely have the testbench
    # print out the outputs as a JSON line
    code += "report \"" + outs_report + "\";\n"
  code += "\n"

  return code

# auxiliary function to put a series of assignments (with or without asserts)
# into the skeleton, generating a tb.vhd
def vhdl_fill(ins_list, delay, expects_list=None, outs_names=None):
  contents = ""
  for i in range(len(ins_list)):
    ins = ins_list[i]
    expects = expects_list[i] if expects_list else None
    contents += vhdl_assign(ins, delay, outs_names, expects)
  contents += "report \"%s\";\nwait;\n" % (FINISHED,)
  os.system("cp %s %s" % (SKELETON, TB))
  with open(TB, "r") as f:
    skel = f.read()
  filled = skel.replace(SKEL_LINE, contents).replace("\\n", "\"&LF&\"")
  with open(TB, "w") as f:
    f.write(filled)

# auxiliary function to run a certain "good" assignment and get the outputs
# we expect the tb.vhd to be ready
def vhdl_run(assignment):
  # first, run and get the output
  os.system("cp %s %s" % (assignment, TARGET))
  os.system(";".join([PREPARE, ANALYSE, ELABORATE]))
  run_output = os.popen(RUN).read()
  os.system(CLEANUP)
  # now, get all outputs
  outs = []
  for json_string in re.findall(r"{[^}]*}", run_output):
    outs.append(json.loads(json_string.replace("'", "\"")))
  return outs

# get to know the specs
if len(sys.argv) < 2:
  print("Tell me the specs JSON file!")
  sys.exit(0)

with open(sys.argv[1], "r") as specfile:
  specs = json.load(specfile)

print("Specs read! Generating preliminary...")

# generate the inputs, first by making all the possible values...
values = {}
for name, details in specs["input_sets"].items():
  values[name] = set(details["must_happen"])
  must_have = len(values[name]) + details["randomized"]
  while len(values[name]) != must_have:
    values[name].add(bitstring(details["size"]))

# now, combine them! how elegant... and also make the preliminary testbench
inputs = [dict(zip(values.keys(), l)) for l in product(*values.values())]
vhdl_fill(inputs, specs["lag"], None, specs["outputs"])

print("Done! Total inputs: %s." % (str(len(inputs)),))

# and run every "good" assignment against it
outs = []
for goodname in specs["run_against"]:
  print("Running against %s..." % (goodname,))
  outs.append(vhdl_run(goodname))

# now, remove inputs for which good assignments gave different outputs
disagreements = 0
final_ins = []
final_outs = []
for i in range(len(inputs)):
  opinions = [dude[i] for dude in outs]
  if all(x == opinions[0] for x in opinions) or True:
    final_ins.append(inputs[i])
    final_outs.append(opinions[0])
  else:
    disagreements += 1

print("Removed %s disagreements." % (str(disagreements),))

# and for out final trick, generate the testbench
vhdl_fill(final_ins, specs["lag"], final_outs, specs["outputs"])

print("Final testbench saved to %s!" % (TB,))
