//xartis gia ton xristi
var mymap;
var polId;
var polygons = [];
var num;
var simulationData = [];
var marker;
var markers = [];
var	h_tmp;
var	m_tmp;

//marker gia tin topothesia pou epilegei o xristis
var myIcon1 = L.icon({
    iconUrl: 'images/markericon1.png',
    iconSize: [50, 50],
    iconAnchor: [ 25, 50 ],
    shadowAnchor: [25, 50],
    shadowSize:   [25, 25]
});

//marker gia to simio tou cluster
var myIcon2 = L.icon({
    iconUrl: 'images/markericon2.png',
    iconSize: [50, 50],
    iconAnchor: [ 25, 50 ],
    popupAnchor: [0, -50],
    shadowAnchor: [25, 50],
    shadowSize:   [25, 25]
});

$(document).ready(function() {

    //vazoume trexousa wra gia default wra eksomoiwsis
    var now = new Date();
    var h = now.getHours();
    var m = now.getMinutes();
    var myjson;

    h_tmp = h;
    m_tmp = m;
    if(h_tmp < 10) h_tmp = '0' + h_tmp;
    if(m_tmp < 10) m_tmp = '0' + m_tmp;
    var one = document.getElementById("input-hour");
    var two = document.getElementById("hour");
    one.defaultValue = h_tmp + ":" + m_tmp;
    two.defaultValue = h_tmp + ":" + m_tmp;

    offsetValue = 0;
    offsetType = 0;

    var obj = {hour: h, min: m};
    var points;
    var polygon;
    let cityCoords;
    var i;

    $.ajax({
        url: 'mapdata.php',
        dataType: 'json'
    }).then( function(response1) {
        myjson = response1.data;
        console.log(myjson);
        if(myjson == null ){
            alert("Αδυναμία φόρτωσης χάρτη")
            var element = document.getElementById('loader');
            $('#mapid').html('<p>Σφάλμα φόρτωσης χάρτη και δεδομένων.</p>');
            element.parentNode.removeChild(element);
            return;
        }
        mymap = L.map('mapid', {zoomControl: false});
        L.control.zoom({
            position: 'bottomright'
        }).addTo(mymap);
        let osmUrl='https://tile.openstreetmap.org/{z}/{x}/{y}.png';
        let osmAttrib='Map data © <a href="https://openstreetmap.org">OpenStreetMap</a> contributors';
        let osm = new L.TileLayer(osmUrl, {attribution: osmAttrib});

        cityCoords = myjson[0][0];
        num = myjson[0][1]; //arithmo poligwnwn

        //aitima gia na paroume ta stoixia tou kathe poligwnou
        $.ajax({
            url: 'simulation.php',
            dataType: 'json',
            method: 'POST',
            data: obj,
            error: function(){
                mymap.addLayer(osm);
                mymap.setView( [cityCoords['y'],cityCoords['x']] , 14);

                for(i = 0; i < num; i++) {
                    points = [myjson[1][i].map( Object.values )];
                    polygon = L.polygon(points,{id:i, color:  "grey", fillColor:  "grey", opacity: 0.05, fillOpacity:0.5}).addTo(mymap);
                    polygons.push(polygon);
                    polId = polygon.options.id;
                }

                myjson = null;
                cityCoords = null;
                points = null;
                console.log("Done Loading Map");
                var element = document.getElementById('loader');
                element.parentNode.removeChild(element);
            },
            success: function(response2) {
                console.log(" Done simulation");
                mymap.addLayer(osm);
                mymap.setView( [cityCoords['y'],cityCoords['x']] , 14);

                simulationData = response2;
                for(i = 0; i < num; i++) {
                    points = [myjson[1][i].map( Object.values )];
                    if(simulationData[i].id == i) occupied = simulationData[i].occupied_park_spots;
                    else occupied = -1;
                    //xrwmatizoume ta poligwna analoga me tis theseis parking
                    if (occupied >= 0 && occupied <= 59) color = "green";
                    else if (occupied > 59 && occupied <= 84) color = "yellow";
                    else if (occupied > 84 && occupied <= 100) color = "red";
                    else color = "grey";

                    polygon = L.polygon(points,{id:i, color: color, fillColor: color, opacity: 0.05, fillOpacity:0.5}).addTo(mymap);
                    polygon.bindTooltip('<h4 id="theseis" style="background-color: transparent">' + occupied.toFixed(2) + "% Κατειλημμένες θέσεις </h4>(Περίπου " + simulationData[i].free_park_spots.toFixed(0) + " Ελεύθερες θέσεις)");
                    polygons.push(polygon);
                    polId = polygon.options.id;
                }
                myjson = null;
                cityCoords = null;
                points = null;
                $(".availability").css("display","block");
                console.log("Done Loading Map");
                var element = document.getElementById('loader');
                element.parentNode.removeChild(element);
            }
        });

        mymap.doubleClickZoom.disable();
        mymap.on('click', addMarker);
        //mymap.on('dblclick', removeMarker);

    }, function() {
        console.log(response.responseText, error);
        var element = document.getElementById('loader');
        element.parentNode.removeChild(element);
        $('#mapid').html('<p>Σφάλμα φόρτωσης χάρτη και δεδομένων. <br>' + response.status + " " + error + '</p>');
    });
});

function availability(){
    var color;
    var occupied = 0;
    //console.log(hour)

    var hour = $("#input-hour").val();
    //console.log(hour);

    if( hour === "") {
        return;
    }

    $("#avail").html('<img src="images/ajax-loader.gif" /> &nbsp; Φόρτωση...');
    simulation(hour);
}

function simulation(hour) {
    var h = Number( hour.split(':')[0] ) ;
    var m = Number( hour.split(':')[1] );

    var obj = {hour : h, min: m};

    $.ajax({
        url: 'simulation.php',
        method: "POST",
        data: obj,
        dataType: 'json',
        error: function(request, status, error) {
            //alert(request.status + error);
            console.log(request.responseText + status + error);
            $("#avail").html('Δείτε Διαθεσιμότητα');
        },
        success: function(response) {
            console.log(" Done simulation");
            simulationData = response;
            //console.log(simulationData);

            for(i=0; i<num; i++) {
                if(simulationData[i].id == i)
                    occupied = simulationData[i].occupied_park_spots;
                else
                    occupied = -1;

                if (occupied >= 0 && occupied <= 59) color = "green";
                else if (occupied > 59 && occupied <= 84) color = "yellow";
                else if (occupied > 84 && occupied <= 100) color = "red";
                else color = "grey";
                polygons[i].setStyle({color: color, fillColor: color, opacity: 0.05, fillOpacity:0.5});
            }
            $("#avail").html('Δείτε Διαθεσιμότητα');
        }
    });
}

function addMarker(e) {
    clearMap();
    let i = 0;

    var coords = Object.values(e.latlng);
    console.log(coords);

    if (marker !== undefined) marker.setLatLng(coords)
    else {
        marker = L.marker(coords, {icon: myIcon1}).addTo(mymap);
    }
    marker.on('click', removeMarker);
    for(i=0; i< num; i++) {
        polygons[i].unbindTooltip();
    }
    /*var markerBounds = L.latLngBounds([marker.getLatLng()]);
    mymap.fitBounds(markerBounds);*/
    $("#find-park").css("display", "inline-block");
}

function removeMarker(e) {
    if (marker !== undefined) {
        marker.removeFrom(mymap);
        marker = undefined;
        $("#find-park").css("display", "none");
        $("#hour").val( h_tmp + ":" + m_tmp);
        $("#distance").val("");
        //var two = document.getElementById("hour");
    }
    let i = 0;
    for(i=0; i< num; i++) {
        polygons[i].bindTooltip('<h4 id="theseis" style="background-color: transparent">' +  simulationData[i].occupied_park_spots.toFixed(2) + "% Κατειλημμένες θέσεις </h4>(Περίπου " + simulationData[i].free_park_spots.toFixed(0) + " Ελεύθερες θέσεις)");
    }
    clearMap();
}

function clearMap() {
    var i;
    var lenPM = markers.length;
    if ( lenPM != 0 ) {
        for(i=0; i < lenPM; i++) {
            markers[i].removeFrom(mymap);
        }
        markers = [];
    }
    console.log(markers);
}
/* Testing */
$(document).on("submit", "#find-form", function(e){
    e.preventDefault();
    $("#avail2").html('<img src="images/ajax-loader.gif" /> &nbsp; Φόρτωση...');
    mymap.off('click', addMarker);
    marker.off('click', removeMarker);

    var position = Object.values(marker.getLatLng());
    var hour = $("#hour").val();
    var dist = $("#distance").val();
    console.log(position);
    console.log(hour);
    console.log(dist);

    var h = Number( hour.split(':')[0] ) ;
    var m = Number( hour.split(':')[1] );

    var obj = {hour : h, min: m};

    $.ajax({
        url: 'simulation.php',
        method: "POST",
        data: obj,
        dataType: 'json',
        error: function(request, status, error) {
            //alert(request.status + error);
            console.log(request.responseText + status + error);
            $("#avail2").html('Βρείτε Θέση');
        },
        success: function(response) {
            console.log(" Done simulation");
            simulationData = response;
            //console.log(simulationData);
            var obj2 = {position: position, r: dist};
            console.log(obj2);

            $.ajax({
                //aitima gia euresis thesis stauthmeusis
                url: 'suggest_parking.php',
                method: "POST",
                data: obj2,
                dataType: 'json',
                error: function(request, status, error) {
                    $("#avail2").html('Βρείτε Θέση');
                    mymap.on('click', addMarker);
                    marker.on('click', removeMarker);
                    alert(request.status + error);
                    console.log(request.responseText + status + error);
                },
                success: function(response2) {
                    mymap.on('click', addMarker);
                    marker.on('click', removeMarker);
                    $("#avail2").html('Βρείτε Θέση');
                    console.log(response2);
                    var cluster_centroid = response2;
                    len = cluster_centroid.length;

                    if (len === 0){
                        alert("Δεν βρέθηκαν προτεινόμενες θέσεις στάθμευσης. Ξαναδοκιμάστε αλλάζοντας την επιθυμητή μέγιστη απόσταση.");
                    }
                    else {
                        //gia ola ta max clusters pou vrethikan emfanizete popup
                        //me tin apostasi se metra apo ton xristi mexri to simio pou vrethike
                        for (i = 0; i < len; i++) {
                            let m = L.marker([cluster_centroid[i].centroid_Y, cluster_centroid[i].centroid_X], {icon: myIcon2});
                            m.addTo(mymap).bindPopup('<h3 style="background-color: transparent">Σε απόσταση περίπου '+ cluster_centroid[i].distance.toFixed(2) + ' μέτρα</h3>').openPopup();;
                            markers.push(m);
                        }
                    }
                    console.log(markers);
                }
            });

            for(i=0; i<num; i++) {
                if(simulationData[i].id == i)
                    occupied = simulationData[i].occupied_park_spots;
                else
                    occupied = -1;

                if (occupied >= 0 && occupied <= 59) color = "green";
                else if (occupied > 59 && occupied <= 84) color = "yellow";
                else if (occupied > 84 && occupied <= 100) color = "red";
                else color = "grey";
                polygons[i].setStyle({color: color, fillColor: color, opacity: 0.05, fillOpacity:0.5});
            }
        }
    });
});