// set elements to display like terminal text in case they are terminal text
let outs = document.getElementsByClassName("output");
for (let key in outs) {
  if (!outs.hasOwnProperty(key)) continue;
  if (outs[key].innerText.indexOf("vhd") > -1) {
    outs[key].classList.add("term");
    // highlight line numbers
    outs[key].innerHTML = outs[key].innerHTML.replace(
      /(target.vhd:\d+)/g, "<b>$1</b>");
  }
}
