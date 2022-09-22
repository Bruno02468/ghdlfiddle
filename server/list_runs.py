#!/usr/bin/env python3
# borges 2022
# generates a list of runs to use in graphs and stuff

import sys
import sqlite3
import json
from datetime import datetime


DATABASE_FILE = "database.db"


def get_jobs():
  result = {
    "generated": datetime.now().isoformat(),
    "runs": []
  }
  cxn = sqlite3.connect(DATABASE_FILE)
  c = cxn.cursor()
  keys = ["job_id", "time", "code"]
  commas = ", ".join(keys)
  query = f"SELECT {commas} FROM reports ORDER BY time DESC;"
  for row in c.execute(query):
    obj = {}
    for k, v in zip(keys, row):
      obj[k] = v
    result["runs"].append(obj)
  return result


def main():
  json.dump(get_jobs(), sys.stdout)
  print("")


if __name__ == "__main__":
  main()
