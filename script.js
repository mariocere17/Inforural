let btn = document.getElementById("enviar");
let div = document.getElementById("provincias");
let selector = document.getElementById("selector");
let selectorProvincias = document.getElementById("provincias");
selector.style.display = "none";

selectorProvincias.addEventListener("change",(ev)=>{
    let op = ev.target.value;
    let xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("selector").innerHTML = this.responseText;
            selector.style.display = "inline";
        }
    };
    xhttp.open("GET",`Carga_Selectores.php?prov=${op}`, true);
    xhttp.send();
});
