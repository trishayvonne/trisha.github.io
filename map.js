var polId;
var polygons = [];
var num;

$(document).ready(function() {
    //1. loading the paper with the polygons for the parser
    //2. polygon processing
    $.ajax({
        url: 'mapdata.php',
        dataType: 'json',
        error: function(response, error) {
            console.log(response.responseText, error);
            $('#mapid').html('<p>Error loading map and data. <br>' + response.status + " " + error + '</p>');
        },
        success: function(response) {
            if(response.error == true){
                $('#mapid').html('<h2>Error loading map and data: <br>' + response.msg + '</h2>');
                console.log(response.error_msg);
            }
            else if(response.error == false) {
                var myjson = response.data;
                var tmp;
                var points;
                var polygon;
                let cityCoords;
                let i;

                mymap = L.map('mapid', {zoomControl: false});
                L.control.zoom({
                    position: 'bottomright'
                }).addTo(mymap);

                let osmUrl='https://tile.openstreetmap.org/{z}/{x}/{y}.png';
                let osmAttrib='Map data © <a href="https://openstreetmap.org">OpenStreetMap</a> contributors';
                let osm = new L.TileLayer(osmUrl, {attribution: osmAttrib});

                mymap.addLayer(osm);

                cityCoords = myjson[0][0];
                mymap.setView( [cityCoords['y'],cityCoords['x']] , 14);

                num = myjson[0][1];

                for(i = 0; i < num; i++) { //for all polygons to become gray in color
                    points = [myjson[1][i].map( Object.values )];
                    polygon = L.polygon(points,{id:i, color:"grey", fillColor:"grey", opacity: 0.1, fillOpacity:0.5}).addTo(mymap);
                    polygons.push(polygon);
                    polId = polygon.options.id;
                    polygon.on('click', openPopupF);
                }
                loadParkingSpots();
                myjson = null;
                cityCoords = null;
                points = null;
                console.log("Done Loading Map");
                var element = document.getElementById('loader');
                element.parentNode.removeChild(element);
            }
        }
    });
});

//Popup for entering demand curve data of each polygon
function popupBind(polygon) {

    polId = polygon.options.id;
    var numOfParkSpots = polygon.options.park;
    if (numOfParkSpots === null) numOfParkSpots = "-";

    var headerInfo = "<h3 id='header-info'> ENTER SQUARE INFORMATION: " + polId + "</h3>";
    var template1 = "<h3 class='info'>PARKING SPACES: </h3>";
    var template2 = '<form id="form-1">\
  <label id="inp2" for="input2">Lots of parking spaces: &nbsp' + numOfParkSpots  + '</label>\
  <input id="input2" class="input" type="number" min=0 max=1000 required /><br>\
  <button class="save-but" type="submit">Save</button></form><h3 id="result1" class="info"></h3><hr>\
  <form id="form-2"> <h3 class="info"> DEMAND CURVE DATA: </h3>';

    var template3 = '<div class="container-form-custom"><div id="buttons-top">\
  <button id="center-dem" type="button" onclick="preDemand(1)" >Center</button><button id="resid-dem" type="button" onclick="preDemand(2)" >Residence</button><button id="const-dem" type="button" onclick="preDemand(3)" >Stable</button></div>\
  <div class="block1"><div class="firstrow"><h4>Time</h4><h4>Percent</h4></div>\
  <div class="row-custom "><div class="col1"><label id="label_hour" for="H0"> 00:00 </label></div><div class="col2"><input id= "H0" class="input" type="number" step="0.01" max="1" min="0" name="demand" required /></div></div>\
  <div class="row-custom"><div class="col1"><label id="label_hour" for="H1"> 01:00 </label></div><div class="col2"><input id= "H1" class="input" type="number" step="0.01" max="1" min="0"name="demand" required /></div></div>\
  <div class="row-custom"><div class="col1"><label id="label_hour" for="H2"> 02:00 </label></div><div class="col2"><input id= "H2" class="input" type="number" step="0.01" max="1" min="0" name="demand" required /></div></div>\
  <div class="row-custom"><div class="col1"><label id="label_hour" for="H3"> 03:00 </label></div><div class="col2"><input id= "H3" class="input" type="number" step="0.01" max="1" min="0" name="demand" required /></div></div>\
  <div class="row-custom"><div class="col1"><label id="label_hour" for="H4"> 04:00 </label></div><div class="col2"><input id= "H4" class="input" type="number" step="0.01" max="1" min="0" name="demand" required /></div></div>\
  <div class="row-custom"><div class="col1"><label id="label_hour" for="H5"> 05:00 </label></div><div class="col2"><input id= "H5" class="input" type="number" step="0.01" max="1" min="0" name="demand" required /></div></div>\
  <div class="row-custom"><div class="col1"><label id="label_hour" for="H6"> 06:00 </label></div><div class="col2"><input id= "H6" class="input" type="number" step="0.01" max="1" min="0" name="demand" required /></div></div>\
  <div class="row-custom"><div class="col1"><label id="label_hour" for="H7"> 07:00 </label></div><div class="col2"><input id= "H7" class="input" type="number" step="0.01" max="1" min="0" name="demand" required /></div></div>\
  <div class="row-custom"><div class="col1"><label id="label_hour" for="H8"> 08:00 </label></div><div class="col2"><input id= "H8" class="input" type="number" step="0.01" max="1" min="0" name="demand" required /></div></div>\
  <div class="row-custom"><div class="col1"><label id="label_hour" for="H9"> 09:00 </label></div><div class="col2"><input id= "H9" class="input" type="number" step="0.01" max="1" min="0" name="demand" required /></div></div>\
  <div class="row-custom"><div class="col1"><label id="label_hour" for="H10"> 10:00 </label></div><div class="col2"><input id= "H10" class="input" type="number" step="0.01" max="1" min="0" name="demand" required /></div></div>\
  <div class="row-custom"><div class="col1"><label id="label_hour" for="H11"> 11:00 </label></div><div class="col2"><input id= "H11" class="input" type="number" step="0.01" max="1" min="0" name="demand" required /></div></div></div>\
  <div class="block2"><div class="firstrow"><h4>Time</h4><h4>Rate</h4></div>\
  <div class="row-custom"><div class="col1"><label id="label_hour" for="H12"> 12:00 </label></div><div class="col2"><input id= "H12" class="input" type="number" step="0.01" max="1" min="0" name="demand" required /></div></div>\
  <div class="row-custom"><div class="col1"><label id="label_hour" for="H13"> 13:00 </label></div><div class="col2"><input id= "H13" class="input" type="number" step="0.01" max="1" min="0" name="demand" required /></div></div>\
  <div class="row-custom"><div class="col1"><label id="label_hour" for="H14"> 14:00 </label></div><div class="col2"><input id= "H14" class="input" type="number" step="0.01" max="1" min="0" name="demand" required /></div></div>\
  <div class="row-custom"><div class="col1"><label id="label_hour" for="H15"> 15:00 </label></div><div class="col2"><input id= "H15" class="input" type="number" step="0.01" max="1" min="0" name="demand" required /></div></div>\
  <div class="row-custom"><div class="col1"><label id="label_hour" for="H16"> 16:00 </label></div><div class="col2"><input id= "H16" class="input" type="number" step="0.01" max="1" min="0" name="demand" required /></div></div>\
  <div class="row-custom"><div class="col1"><label id="label_hour" for="H17"> 17:00 </label></div><div class="col2"><input id= "H17" class="input" type="number" step="0.01" max="1" min="0" name="demand" required /></div></div>\
  <div class="row-custom"><div class="col1"><label id="label_hour" for="H18"> 18:00 </label></div><div class="col2"><input id= "H18" class="input" type="number" step="0.01" max="1" min="0" name="demand" required /></div></div>\
  <div class="row-custom"><div class="col1"><label id="label_hour" for="H19"> 19:00 </label></div><div class="col2"><input id= "H19" class="input" type="number" step="0.01" max="1" min="0" name="demand" required /></div></div>\
  <div class="row-custom"><div class="col1"><label id="label_hour" for="H20"> 20:00 </label></div><div class="col2"><input id= "H20" class="input" type="number" step="0.01" max="1" min="0" name="demand" required /></div></div>\
  <div class="row-custom"><div class="col1"><label id="label_hour" for="H21"> 21:00 </label></div><div class="col2"><input id= "H21" class="input" type="number" step="0.01" max="1" min="0" name="demand" required /></div></div>\
  <div class="row-custom"><div class="col1"><label id="label_hour" for="H22"> 22:00 </label></div><div class="col2"><input id= "H22" class="input" type="number" step="0.01" max="1" min="0" name="demand" required /></div></div>\
  <div class="row-custom"><div class="col1"><label id="label_hour" for="H23"> 23:00 </label></div><div class="col2"><input id= "H23" class="input" type="number" step="0.01" max="1" min="0" name="demand" required /></div></div>\</div></div>\
  <div id="buttons1"> <button class="demandButton" onclick="loadDemand(polId)" type="button"> Load Saved Request Data</button></div>\
  <div id="buttons2"> <button type="submit"> Save</button>\
  <button type="reset" value="Reset"> Clearance Form </button></div>\
  </form> <h3 id="result2" class="info"></h3>';

    var content = headerInfo + template1 + template2 + template3;
    polygon.bindPopup(content, {id : polId, maxWidth: 300});
}

function openPopupF(event) {
    var polygon = event.target;
    polId = polygon.options.id;
    polygon.openPopup();
}

$(document).on("submit","#form-1",function(e) {
    e.preventDefault();
    var id = polId;
    var input =  $("#form-1 input[id=input2").val();
    //console.log(input);

    if(input === "") {
        alert("Δώστε πλήθος θέσεων στάθμευσης");
        return false;
    }

    input = Number(input);
    var form_data =  { "id":id, "parkspots":input };

    //Aitima ajax gia na apothikeusoume tis theseis stathmeusis gia to poligwno
    $.ajax({
        type: "POST",
        url: 'saveparkingspots.php',
        dataType: 'json',
        cache: false,
        data: form_data,
        error: function(request, status, error) {
            console.log(request.responseText + status + error);
            $("#result1").css("display", "block");
            $("#result1").html("Σφάλμα αποθήκευσης");
            $("#result1").css( "color", "red");
            setTimeout( function() { $("#result1").css("display", "none"); $("#result1").empty(); }, 3000);
        },
        success: function(response){
            if(response.error == false) {
                document.getElementById("inp2").innerHTML = ("Πλήθος θέσεων στάθμευσης: &nbsp" + input);
                $("#result1").css("display", "block");
                $("#result1").html("Επιτυχής Αποθήκευση");
                $("#result1").css( "color", "green");
                setTimeout( function() { $("#result1").css("display", "none"); $("#result1").empty(); }, 3000);
                //alert(response.msg);
                polygons[id].options.park = input;
                popupBind(polygons[id]);
            }
            else {
                alert(response.msg);
                $("#result1").css("display", "block");
                $("#result1").html("Σφάλμα αποθήκευσης");
                $("#result1").css( "color", "red");
                setTimeout( function() { $("#result1").css("display", "none"); $("#result1").empty(); }, 3000);
                console.log(response.error_msg);
            }
        }
    });
});


$(document).on("submit","#form-2",function(e) {
    e.preventDefault();
    var id = polId;
    var values = [];
    let i = 0;
    let val = 0;

    var demandInput = $("#form-2 input[name=demand]");
    var demInputLen = demandInput.length;

    for (i = 0; i < demInputLen; i++ ) {
        val = demandInput[i].value;
        if (val === "") {
            alert("Συμπλήρωσε όλα τα ποσοστά");
            return false;
        }
        else
            values.push( Number(val) );
    }

    var form_data = { id: id, valuesPerH: values };

    $.ajax({
        type: "POST",
        url: 'savedemand.php',
        dataType: 'json',
        cache: false,
        data: form_data,
        error: function(request, status, error) {
            console.log(request.responseText + status + error);
            $("#result2").css("display", "block");
            $("#result2").html("Σφάλμα αποθήκευσης");
            $("#result2").css( "color", "red");
            setTimeout( function() { $("#result2").css("display", "none"); $("#result2").empty(); }, 3000);
        },
        success: function(response){
            if(response.error == false) {
                $("#result2").css("display", "block");
                $("#result2").html("Επιτυχής Αποθήκευση");
                $("#result2").css( "color", "green");
                setTimeout( function() { $("#result2").css("display", "none"); $("#result2").empty(); }, 3000);

                //alert(response.msg.join(''));
                polygons[id].options.demand = values;
            }
            else {
                $("#result2").css("display", "block");
                $("#result2").html("Σφάλμα αποθήκευσης");
                $("#result2").css( "color", "red");
                setTimeout( function() { $("#result2").css("display", "none"); $("#result2").empty(); }, 3000);

                // alert("Προέκυψε σφάλμα(" + response.num_of_errors + "): " + response.error_pos.join() +".\n" + response.msg.join('') );
                console.log(response.the_errors);
            }
        }
    });
});

//Sinartisi gia na emfanizoume tis idi apothikeumenes theseis stathmeusis pou iparxoun gia to poligwno
//tis diavazoume apo to json arxeio pou dimiourgume sto loadparkspots.php
function loadParkingSpots() {
    var x = 0;
    var polsLen = polygons.length;

    $.ajax({
        url: 'loadparkspots.php',
        dataType: 'json',
        error: function(request, status, error) {
            alert("Αδυναμία φόρτωσης δεδομένων θέσεων στάθμευσης." + "\n" + request.status + error);
            console.log(request.responseText + status + error);
            for(x = 0; x < polsLen; x++) {
                polygons[x].options.park = null;
                popupBind(polygons[x]);
            }
        },
        success: function(response) {
            if (response.error == true) {
                alert("Αδυναμία φόρτωσης δεδομένων θέσεων στάθμευσης τετραγώνων." + "\n" + response.msg);
                console.log(response.error_msg);
                for(x = 0; x < polsLen; x++ ) {
                    polygons[x].options.park = null;
                    popupBind(polygons[x]);
                }
            }
            else if(response.error == false) {
                //console.log(response.msg);
                data = response.data;
                for(x = 0; x < polsLen; x++) {
                    polygons[x].options.park = data[x];
                    popupBind(polygons[x]);
                }
            }
            data = null;
        }
    });
}

//sinartisi gia na fortwthoun ta dedomena kampilwn zitisis tou poligwnou an iparxoun idi stin vasi
function loadDemand(polID) {
    var i=0;
    var elemId;
    var demandData;

    if(polygons[polID].options.demand !== undefined) {
        demandData = polygons[polID].options.demand;
        for(i = 0; i < 24; i++){
            elemId = "#H" + i;
            $(elemId).val(demandData[i]);
        }
        return;
    }

    var dataObj = {id: polID};

    $.ajax({
        url: 'loaddemand.php',
        method: "POST",
        data: dataObj,
        dataType: 'json',
        error: function(request, status, error) {
            alert("Αδυναμία φόρτωσης δεδομένων ζήτησης για το τετράγωνο " + polID + "." + "\n" + request.status + " " + error);
            console.log(status + error);
        },
        success: function(response) {
            if (response.error == true) {
                alert("Αδυναμία φόρτωσης δεδομένων ζήτησης για το τετράγωνο " + polID + "." + "\n");
                console.log(response.error_msg);
            }
            else {
                demandData = response.data;

                if(demandData === null) {
                    alert("Δεν υπάρχουν αποθηκευμένα δεδομένα ζήτησης για το τετράγωνο " + polID);
                    return;
                }

                for(i = 0; i < 24; i++){
                    elemId = "#H" + i;
                    $(elemId).val(demandData[i]);
                }
                polygons[polID].options.demand = demandData;
            }
        }
    });
}

//proepilegmenes kampiles zitisis ana kentro, katoikia kai statheri
function preDemand(type){
    var demandDtmp;
    var elemId;
    if(type==1) demandDtmp = [0.75, 0.55, 0.46, 0.19, 0.2, 0.2, 0.39, 0.55, 0.67, 0.8, 0.95, 0.9, 0.95, 0.9, 0.88, 0.83, 0.7, 0.62, 0.74, 0.8, 0.8, 0.95, 0.92, 0.76];
    else if(type==2) demandDtmp = [0.69, 0.71, 0.73, 0.68, 0.69, 0.7, 0.67, 0.55, 0.49, 0.43, 0.34, 0.45, 0.48, 0.53, 0.5, 0.56, 0.73, 0.41, 0.42, 0.48, 0.54, 0.6, 0.72, 0.66];
    else if(type==3) demandDtmp = [0.18, 0.17, 0.21, 0.25, 0.22, 0.17, 0.16, 0.39, 0.54, 0.77, 0.78, 0.83, 0.78, 0.78, 0.8, 0.76, 0.78, 0.79, 0.84, 0.57, 0.38, 0.24, 0.19, 0.23];

    for(i = 0; i < 24; i++){
        elemId = "#H" + i;
        $(elemId).val(demandDtmp[i]);
    }
}