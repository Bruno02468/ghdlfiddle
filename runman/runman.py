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
DATABASE_FILE = "../database.db"
JOB_STATUS_QUEUED = 0
JOB_STATUS_RUNNING = 1
JOB_STATUS_DONE = 2
ANALYSE = "ghdl -a target.vhd > a.log 2>&1"
ELABORATE = "ghdl -e tb > e.log 2>&1"
RUN = "ghdl -r tb --vcd=out.vcd > r.log 2>&1"
TOTAL_COMMAND = "%s && %s && %s" % (ANALYSE, ELABORATE, RUN)
SANDBOX="sandbox"
ZIPFILE = SANDBOX + "/tb.zip"

import sqlite3
import sys
import os
from subprocess import STDOUT, check_output
from shlex import quote

# run a command safely with timeout and get the output
def runsafe(command, seconds):
  return check_output("firejail --private=%s %s" % (SANDBOX, quote(command)),
                      stderr=STDOUT, timeout=seconds)

# we'll use the same connection throughout our entire run, maybe locks can make
# use of the fact we only drop the connection after we're wholly done?
cxn = sqlite3.connect(DATABASE_FILE)

# fetch the top job which is queued
c = cxn.cursor()
c.execute("SELECT job_id, hint, code, testbench_id FROM jobs WHERE status=?;"
          (JOB_STATUS_QUEUED,))
job_id, hint, job_code, tb_id = c.fetchone()
c.close()

# set it as RUNNING
c = cxn.cursor()
c.execute("UPDATE jobs SET status=? WHERE job_id=?;",
          (JOB_STATUS_RUNNING, job_id))
cxn.commit()
c.close()

# prepare the report
meta = "No system report at this time."
analysis = "Your code could not ne analysed."
compilation = "Your code could not be compiled."
execution = "Your code could not be executed."

# get the relevant testbench
c = cxn.cursor()
c.execute("SELECT zipped FROM testbenches WHERE testbench_id=?;", (tb_id,))
tb_row = c.fetchone()
c.close()

# how did they manage to fail this hard?
if not tb_row:
  meta = "The selected testbench does not exist."
  finish()

# okay, fetch the testbench zipfile, and unpack it
b64_zipped_tb = tb_row[0]
zipfile = open(ZIPFILE, "wb")
zipfile.write(base64.b64decode(b64_zipped_tb))
zipfile.close()
runsafe("unzip tb.zip -d . && rm tb.zip", 2)

# first, we put the user's code into a file too
userfile = open("target.vhd", "w")
userfile.write(job_code)
userfile.close()

# now, we analyse, elaborate, and run the simulation
runsafe(TOTAL_COMMAND, 5)
analysis = runsafe("cat a.log")
compilation = runsafe("cat e.log")
execution = runsafe("cat r.log")
done = runsafe("date", 1)

metas = []
if analysis == "":
  metas.append("Analysis output empty; probably successful.")
if compilation == "":
  metas.append("Compilation output empty; probably successful.")
meta = "\n".join(metas)

def finish():
  # put these into a report
  c = cxn.cursor()
  c.execute(("INSERT INTO reports (meta, analysis, compilation, execution, time) "
            "VALUES (?,?,?,?,?)"), (meta, analysis, compilation, execution, done))
  c.execute("UPDATE jobs SET status=? WHERE job_id=?;" (job_id, JOB_STATUS_DONE))
  cxn.commit()
  c.close()
  cxn.close()
  sys.exit(0)
