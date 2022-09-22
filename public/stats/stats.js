// borges 2022
// generate and display a chart with runs per day, etc.
// no, I still hate JS

// some elements
const in_start = document.getElementById("start_date");
const in_end = document.getElementById("end_date");
const chart_canvas = document.getElementById("chart");
const waiting = document.getElementById("waiting");

// some globals
let runs = null;
let per_day = {};
let selected_days = null;
let chart = null;

// request the runs json from the server
function fetch_runs() {
  fetch("./stats.json")
    .then((response) => response.json())
    .then(function(obj) {
      runs = obj;
      got_runs()
    });
}

// convert ISO date strings in the json to actual Date objects
function convert_times() {
  runs["generated"] = new Date(Date.parse(runs["generated"]));
  for (const run of runs["runs"]) {
    const ds = run["time"].trim().slice(4);
    run["time"] = new Date(Date.parse(ds));
  }
}

// runs contain a "finalcode" thing because I didn't know sqlite had enums back
// then. so this little function "interprets" the meaning of the finalcode.
// that's how we know if a run was successful or not.
function interpret_finalcode(code) {
  if (code < 0) return "bad";
  if (code > 0) return "good";
  return "unsure";
}

// this ensures that there will be an entry for a given date
function ensure_date(year, month, day) {
  if (!(year in per_day)) per_day[year] = {};
  if (!(month in per_day[year])) per_day[year][month] = {};
  if (!(day in per_day[year][month])) per_day[year][month][day] = {
    "total": 0,
    "good": 0,
    "bad": 0,
    "unsure": 0
  };
}

// ditto, but it takes a date object
function ensure_date_obj(date) {
  [year, month, day] = date_extract(date);
  ensure_date(year, month, day);
}

// returns the ISO date for a Date object
function iso_date(date) {
  return date_extract(date).join("-");
}

// extracts year, month and day as zero-padded strings from a Date object.
// those are what we use for keys in the per_day object.
function date_extract(date) {
  const year = "" + date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  return [year, month, day];
}

// this uses the runs object to generate the per_day object's entries.
function gen_per_day() {
  per_day = {};
  for (const run of runs["runs"]) {
    const rdate = run["time"];
    ensure_date_obj(rdate);
    per_day[year][month][day]["total"] += 1;
    per_day[year][month][day][interpret_finalcode(run["code"])] += 1;
  }
}

// called when one of the date input changes.
function date_changed() {
  // ensure the date range
  let start_date = new Date(Date.parse(in_start.value));
  const end_date = new Date(Date.parse(in_end.value));
  selected_days = [];
  while (start_date <= end_date) {
    selected_days.push(start_date);
    ensure_date_obj(start_date);
    const added = new Date(start_date.setDate(start_date.getDate() + 1));
    start_date = added;
  }
  // update the chart
  update_chart();
}

function init_chart() {
  const config = {
    type: "bar",
    data: gen_dataset(),
    options: {
      plugins: {
        title: {
          display: true,
          text: "ghdlfiddle runs"
        },
      },
      responsive: true,
      scales: {
        x: {
          stacked: true,
        },
        y: {
          stacked: true
        }
      }
    }
  };
  chart = new Chart(chart_canvas.getContext("2d"), config);
}

// gets the run stats for a given date
function of_date(date) {
  ensure_date_obj(date);
  [year, month, day] = date_extract(date);
  return per_day[year][month][day];
}

// generates the data item for the chart object
function gen_dataset() {
  const labels = selected_days.map(iso_date);
  let successful = [];
  let failed = [];
  let unsure = [];
  for (date of selected_days) {
    const r = of_date(date);
    successful.push(r["good"]);
    failed.push(r["bad"]);
    unsure.push(r["unsure"]);
  }
  const data = {
    labels: labels,
    datasets: [
      {
        label: "successful",
        data: successful,
        backgroundColor: "green"
      },
      {
        label: "failed",
        data: failed,
        backgroundColor: "red"
      },
      {
        label: "inconclusive",
        data: unsure,
        backgroundColor: "orange"
      }
    ]
  };
  return data;
}

// generates a new dataset with the given selected days and updates the chart
function update_chart() {
  if (!chart) return;
  chart.data = gen_dataset();
  chart.update();
}

// sets initial values for the date inputs
function set_defaults() {
  let today = new Date();
  in_end.value = iso_date(today);
  today.setDate(today.getDate() - 30);
  in_start.value = iso_date(today);
}

// change the waiting text when we got the data
function change_waiting() {
  waiting.innerText = "Stats generated "
    + runs["generated"].toDateString()
    + ".";
}

// called when the runs JSON arrives. basically sets up the whole page.
function got_runs() {
  // prepare by converting the ISO dates to actual Date objects
  convert_times();
  // pretend the date's changed
  date_changed();
  // do the per-day run counts
  gen_per_day();
  // initialize the chart
  init_chart();
  // set the gen date
  change_waiting();
}

// prepare the whole page
function prepare() {
  // set defaults for the inputs
  set_defaults();
  // fetch the runs and get this party going
  fetch_runs();
}

// hell yeah
prepare();
