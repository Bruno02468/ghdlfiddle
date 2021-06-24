// coded by bruno borges paschoalinoto 2019

const tb_sel = document.getElementById("testbench");
const descs_out = document.getElementById("description");
const dl_link = document.getElementById("dl_link");

function update_description() {
  const tb_id = parseInt(tb_sel.value);
  descs_out.innerText = descriptions[tb_id];
  dl_link.href = "admin/download_tb.php?id=" + tb_id;
}

update_description();
