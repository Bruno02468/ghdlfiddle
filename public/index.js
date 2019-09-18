// coded by bruno borges paschoalinoto 2019

let tb_sel = document.getElementById("testbench");
let descs_out = document.getElementById("description");

function update_description() {
  descs_out.innerText = descriptions[parseInt(tb_sel.value)];
}
