const form = document.querySelector(".typing-area"),
inputField = form.querySelector(".input-field"),
sendBtn = form.querySelector("button");

sendBtn.onclick = () => {
    // let's start ajax
    let xhr = new XMLHttpRequest(); //creating XML object
    xhr.open("POST", "", true);
    xhr.onload = () => {
        if(xhr.readyState === XMLHttpRequest.DONE){
            if(xhr.status === 200){
                let data = xhr.response;
                console.log(data);
                if(data == "success"){
                    location.href = "";
                }else{
                    errorText.style.display = "block";
                    errorText.textContent = data;
                }
            }
        }
    }
    // we have to send the form data through ajax to php
    let formData = new FormData(form); // create new formData Object
    xhr.send(formData); // sending the form data to php
}