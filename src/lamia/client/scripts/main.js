window.addEventListener("popstate", function () {  navigator("popstate");});

function navigator(type) {
    let urlString = window.location.search;
    let urlVariables = new URLSearchParams(urlString);
    let page = urlVariables.get("site") + "/" + urlVariables.get("subsite");
    if(type == "popstate")
    {
       loadPage(page, null);
    }
}


function loadXHR(url, id, type, data, refresh, afterFunc) {
    var xhr = new XMLHttpRequest()
    xhr.onreadystatechange = function () {
        if (xhr.readyState == XMLHttpRequest.DONE) {
            document.getElementById(id).innerHTML = xhr.responseText;
            let funks = document.getElementById(id).getElementsByTagName("script");
            if (afterFunc != null)
                afterFunc();
        }
    }
    xhr.open(type, url, true);
    if(type == "POST")
        xhr.send(data);
    else
        xhr.send();
}


function submitForm(e){
    if(e != null){
        e.preventDefault();
        e.stopPropagation();
    }
    var responseHolder = document.getElementById("responseHolder");
    responseHolder.style.maxHeight = "100px";
    
    let form = document.getElementsByTagName("form")[0];
    let inputs = form.getElementsByTagName("input");
    if(inputs[2].value == "")
    {
        inputs[2].style.backgroundColor = "red";
        inputs[2].focus();
        return;
    }
    else
        inputs[2].style.backgroundColor = "lightgray";
    inputs[inputs.length - 1].disabled = true;
 

    let loadingInterval = loadingArc(responseHolder);

    let afterFunction = function(){
        clearInterval(loadingInterval);
        var startPoint = 100;
        var interval = setInterval(
            function(){
                responseHolder.style.maxHeight = startPoint + "px"; 
                startPoint += 30; 
                if(startPoint > 3000){ 
                    responseHolder.style.maxHeight = none; 
                    clearInterval(interval);
                }},10);

        inputs[inputs.length - 1].disabled = false;
    }

    let formData = new FormData(form);
    formData.append("javascript", true);
    site = origin + "/";
    loadXHR(site, "responseHolder", "POST", formData, false, afterFunction);
}

function linearTransition(element, direction, colors, className){
    var procent;
    var added;
    if(direction)
    {    procent = 0;
        added = 1;
    }
    else
        {
            procent = 100;
        added = -1;
    }
        var interval = setInterval(function(){
            procent +=added;
            element.style.background = "linear-gradient(135deg,"  + colors[0] + " 0%, " + colors[1]+" " +procent + "% ," +  colors[2] + " 100%)";
            if(procent == 100 || procent == 0)
            {
                clearInterval(interval);
                element.className = className;
            }
            
    }, 1);
}

function loadingArc(parentNode){
    var canvas = document.createElement("canvas");
    canvas.setAttribute("width","100px");
    canvas.setAttribute("height","100px");
    parentNode.insertBefore(canvas, parentNode.firstChild);
    canvas.id = "loadingCanvas";
    var ctx = canvas.getContext("2d");
    ctx.beginPath();
    var startAngle = 0;
    var endAngle = 0;
    ctx.arc(50,50, 40, startAngle, endAngle);
    ctx.lineWidth = 8;
    ctx.strokeStyle = "blue";
    ctx.stroke();
    var direction = 0;
    var innerInterval = null;
    var interval = setInterval(function(){
           ctx.beginPath();
            ctx.clearRect(0, 0, 100, 100 );

            ctx.arc(50,50, 40, startAngle * Math.PI, endAngle * Math.PI);
            ctx.stroke();
            if(direction % 2 == 0){
                startAngle += 0.1;
                startAngle = Math.round(startAngle *10)/10;
                if(startAngle >= endAngle + 2 )
                 {endAngle += 0.1;
                    direction +=1;
                 }
            }
            else
            {
                endAngle += 0.1;
                endAngle = Math.round(endAngle *10)/10;
                
                

                if(endAngle >= startAngle)
                {
                    direction +=1;
                startAngle += 0.1;
                }
            }
 
    }, 20);
    return interval;
}

function loadPage(page,e) {
    if(e != null){
        e.preventDefault();
        e.stopPropagation();
    }
    let origin = window.location.origin;
    let data = new FormData();
    let site = "";
    let sites = page.split("/");
    data.append("site", sites[0]);
    data.append("subSite", sites[1]);
    data.append("javascript", true);
    httpHistory = origin + "?site=" + sites[0] + "&subSite=" + sites[1];
    if(e != null){
        window.history.pushState(null, null, httpHistory);
        site = origin + "/";
    }
    let afterFunction = function(){
         let body = document.getElementsByTagName("body")[0];
        if(body.className != sites[0] + "Body"){
            let colors = ["#3f82a1", "#32a864", "#b58a33"];
            let direction;
            if(sites[0] === "book")
                direction = false;
            if(sites[0] === "movie")
                direction = true;
            linearTransition(body, direction, colors, sites[0] + "Body");
        }
    }
    loadXHR(site, "content", "POST", data, false, afterFunction);
    
}


