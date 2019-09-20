#!/usr/bin/env python3
# coded by bruno borges paschoalinoto 2019

# this program is called every once in a while (user must set up a cron job)
# what it does is it takes the first item in the database not marked as "done",
# run it in a safe sandbox, and put a report on the database with the results
# of the analysis and such.

# WARNING: I'm too lazy to implement locking, so it's up to you to make sure no
# two instantes of runman.py are running together at the same time, this may
# duplicate work, lock the database too much, or straight up break stuff.

# WARNING 2: safety and sandboxing are firejail's job, not mine.

# them constants...
DATABASE_FILE = "database.db"
STATUS_QUEUED = 0
STATUS_RUNNING = 1
STATUS_DONE = 2
SETUP = "make setup"
ANALYSE = "ghdl -a target.vhd > a.log 2>&1"
ANALYSE_TB = "ghdl -a tb.vhd"
ELABORATE = "ghdl -e tb > e.log 2>&1"
NO_ANALYSIS = "cat: a.log: No such file or directory"
NO_ELABORATION = "cat: e.log: No such file or directory"
NO_EXECUTION = "cat: r.log: No such file or directory"
RUN = "ghdl -r tb --vcd=out.vcd > r.log 2>&1"
TOTAL_COMMAND = "%s && %s && %s && %s" % (ANALYSE, ANALYSE_TB, ELABORATE, RUN)
SANDBOX="sandbox"
ZIPFILE = SANDBOX + "/tb.zip"
FINALCODE_GOOD = 1
FINALCODE_BAD = -1
FINALCODE_UNSURE = 0

import sqlite3
import sys
import os
import base64
from subprocess import STDOUT, check_output
from shlex import quote

# run a command safely with timeout and get the output
def runsafe(command, seconds):
  return check_output("cd %s && firejail --quiet --private=. %s"
                      % (SANDBOX, command), stderr=STDOUT,
                      timeout=seconds*20, shell=True).decode("utf-8")
def folder_cleanup():
  runsafe("rm -rf ./*", 2)
  runsafe("touch .gitkeep", 2)

folder_cleanup()

# we'll use the same connection throughout our entire run, maybe locks can make
# use of the fact we only drop the connection after we're wholly done?
cxn = sqlite3.connect(DATABASE_FILE)

# fetch the top job which is queued
c = cxn.cursor()
c.execute("SELECT job_id, hint, code, testbench_id FROM jobs WHERE status=?;",
          (STATUS_QUEUED,))
gotjob = c.fetchone()
if not gotjob:
  sys.exit(0)
job_id, hint, job_code, tb_id = gotjob
c.close()

# set it as RUNNING
c = cxn.cursor()
c.execute("UPDATE jobs SET status=? WHERE job_id=?;",
          (STATUS_RUNNING, job_id))
cxn.commit()
c.close()

# prepare the report -- from this point on, we can call finish()
meta = "No system report at this time."
analysis = "Your code could not ne analysed."
compilation = "Your code could not be compiled."
execution = "Your code could not be executed."
finalcode = 0

def finish():
  # put these into a report
  c = cxn.cursor()
  done = runsafe("date", 1)
  c.execute(("INSERT INTO reports (job_id, meta, analysis, compilation, "
            "execution, time, code) VALUES (?,?,?,?,?,?,?)"),
            (job_id, meta, analysis, compilation, execution, done, finalcode))
  c.execute("UPDATE jobs SET status=? WHERE job_id=?;", (STATUS_DONE, job_id))
  if os.path.isfile("%s/out.vcd" % SANDBOX):
    os.system("mv %s/out.vcd >/dev/null 2>&1 ../public/vcd/%s.vcd"
              % (SANDBOX, hint))
    c.execute("UPDATE jobs SET vcd=1 WHERE job_id=?;", (job_id,))
  cxn.commit()
  c.close()
  cxn.close()
  folder_cleanup()
  sys.exit(0)

# get the relevant testbench
c = cxn.cursor()
c.execute("SELECT contents FROM testbenches WHERE testbench_id=?;", (tb_id,))
tb_row = c.fetchone()
c.close()

# how did they manage to fail this hard?
if not tb_row:
  meta = "The selected testbench does not exist."
  finish()

# okay, fetch the testbench zipfile, and unpack it
folder_cleanup()
b64_zipped_tb = tb_row[0]
zipfile = open(ZIPFILE, "wb")
zipfile.write(base64.b64decode(b64_zipped_tb))
zipfile.close()
runsafe("unzip -o tb.zip", 3)

# first, we put the user's code into a file too
userfile = open(SANDBOX + "/target.vhd", "w")
userfile.write(job_code)
userfile.close()

metas = []

# now, we analyse, elaborate, and run the simulation
# yes I know it's lazy and dirty and whatever, sorry not sorry
try:
  runsafe(SETUP, 3)
except:
  metas.append("No setup commands were run.")

try:
  runsafe(TOTAL_COMMAND, 5)
except:
  metas.append("Couldn't complete the three steps.")
  finalcode = -1

analysis = runsafe("cat a.log", 1)
compilation = runsafe("cat e.log", 1)
execution = runsafe("cat r.log", 1)

if analysis == "":
  analysis = "Analysis output empty; probably successful."
if compilation == "":
  compilation = "Compilation output empty; probably successful."

if NO_ANALYSIS in analysis:
  analysis = "No analysis log was generated."
if NO_ELABORATION in compilation:
  compilation = "No compilation log was generated."
if NO_EXECUTION in execution:
  execution = "No execution log was generated."

meta = "<br>".join(metas)

if meta == "":
  meta = "No messages from job manager."


if "ghdlfiddle:GOOD" in execution:
  finalcode = 1
if "ghdlfiddle:BAD" in execution or "three" in meta:
  finalcode = -1

finish()

