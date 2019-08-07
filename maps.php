<?php require_once("FILE_PATH"); ?>


<script>



var map;
var service;
var infowindow;

  function initMap() {
    // map options
    var center = new google.maps.LatLng(49, -100);

    infowindow = new google.maps.InfoWindow();

    // generate the map
    map = new google.maps.Map(document.getElementById('map'), {
      zoom: 4.2,
      center: center,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    });

    // Create the search box and link it to the UI element.
  var input = document.getElementById('pac-input');
  var searchBox = new google.maps.places.SearchBox(input);
  map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

  // Bias the SearchBox results towards current map's viewport.
  map.addListener('bounds_changed', function() {
    searchBox.setBounds(map.getBounds());
  })

  searchBox.addListener('places_changed', function() {
    var places = searchBox.getPlaces();

    if (places.length == 0) {
      return;
    }

    // Clear out the old markers.
    markers.forEach(function(marker) {
      marker.setMap(null);
    });
    markers = [];

    // For each place, get the icon, name and location.
    var bounds = new google.maps.LatLngBounds();
    places.forEach(function(place) {
      if (!place.geometry) {
        console.log("Returned place contains no geometry");
        return;
      }
      var icon = {
        url: place.icon,
        size: new google.maps.Size(71, 71),
        origin: new google.maps.Point(0, 0),
        anchor: new google.maps.Point(17, 34),
        scaledSize: new google.maps.Size(25, 25)
      };

      // Create a marker for each place.
      markers.push(new google.maps.Marker({
        map: map,
        icon: icon,
        title: place.name,
        position: place.geometry.location
      }));


      if (place.geometry.viewport) {
        // Only geocodes have viewport.
        bounds.union(place.geometry.viewport);
      } else {
        bounds.extend(place.geometry.location);
      }
    });
    map.fitBounds(bounds);
  });

    var markers = []; //empty markers array

    // Create a connection to the file.
    var Connect = new XMLHttpRequest();

    // Define which file to open and send the request.
    Connect.open("GET", "FILE_PATH", false);
    Connect.setRequestHeader("Content-Type", "text/xml");
    Connect.send(null);

    // Place the response in an XML document.
    var TheDocument = Connect.responseXML;
    // Place the root node in an element.
    var allMarkers = TheDocument.childNodes[0];

    // Retrieve each customer in turn.
    for (var i = 0; i < allMarkers.children.length; i++){
      var currentMarker = allMarkers.children[i];
      // Access each of the data values.
      // var Name = Customer.getElementsByTagName("Name");

      var latLng = new google.maps.LatLng(
          parseFloat(currentMarker.getAttribute('lat')),
          parseFloat(currentMarker.getAttribute('lng'))
          );

      var marker = new google.maps.Marker({
        position: latLng
      });

      markers.push(marker);
    }

    // Plot everything
    var markerCluster = new MarkerClusterer(map, markers, {imagePath: 'FILE_PATH'});


    //Event Listner: Map bounds change.
    google.maps.event.addListener(map, 'bounds_changed', function(){
      $("#table_body").empty(); //To Do: Revise to empty() 'card'instead of table for mobile
      for(var j = 0; j < allMarkers.children.length; j++){
        var currentMarker = allMarkers.children[j];
        if(isWithinBounds(currentMarker, map)){                    
          //Display on table     Note: markers.xml does not contain picture
          postEntryToTable( currentMarker.getAttribute('id'), currentMarker.getAttribute('first_name') + ' ' + currentMarker.getAttribute('last_name'), currentMarker.getAttribute('role'), (currentMarker.getAttribute('city') +', '+ currentMarker.getAttribute('state')), currentMarker.getAttribute('department'));
        }
      }
    });
  
  }
  function createMarker(place) {
      var marker = new google.maps.Marker({
          map: map,
          position: place.geometry.location
        });

        google.maps.event.addListener(marker, 'click', function() {
          infowindow.setContent(place.name);
          infowindow.open(map, this);
        });
  }

  //Helper Function
  //Returns true if marker is within bounds of map.
  //Paramters: 'marker' is a var with 'lat/lng', map is an initialized map object
  function isWithinBounds(marker, map){
    var latLng = new google.maps.LatLng({lat: parseFloat(marker.getAttribute('lat')), lng: parseFloat(marker.getAttribute('lng')) });
    return map.getBounds().contains(latLng);
  }


  //Formats parameters into <tr> entry and appends row to  <tbody>
  //Paramters:
  function postEntryToTable(id="none", fullName="none", role="none", location="none", department="none", picture_src="none"){
    
    //get table_body element
    var table_body = document.getElementById('ID_TAG_NAME');
    
    //create <tr class="NAME_OF_CLASS"
    var table_row = document.createElement("TR");
    table_row.className = 'NAME_OF_CLASS';  


    //create <td> </td> for name, role, location, department, etc
    var td_Picture = document.createElement('TD');
    var td_Name = document.createElement('TD');
    var td_Role = document.createElement('TD');
    var td_Location = document.createElement('TD');
    var td_Department = document.createElement('TD');


    //add hyperlink to full name
    var link = "URL_PATH" + id;
    td_Name.setAttribute("href", link);


    //create text nodes from parameters
    var text_Picture = document.createTextNode(picture_src); //revise for img src instead of text
    var text_Name = document.createTextNode(fullName);
    var text_Role = document.createTextNode(role);
    var text_Location = document.createTextNode(location);
    var text_Department = document.createTextNode(department);
    

    //append text node to td 
    td_Picture.appendChild(text_Picture); 
    td_Name.appendChild(text_Name);
    td_Role.appendChild(text_Role);
    td_Location.appendChild(text_Location);
    td_Department.appendChild(text_Department);


    //append td to tr
    table_row.appendChild(td_Picture)
    table_row.appendChild(td_Name);   
    table_row.appendChild(td_Role);   
    table_row.appendChild(td_Location);   
    table_row.appendChild(td_Department);


    //append tr to tbody
    table_body.appendChild(table_row);
  }


  function postEntryToCard(id="none", fullName="none", role="none", location="none", department="none", picture_src="none"){
    
    //Get card element [<div class="card" id="card">]
    var card_div = document.getElementById('card');
    
    
    //create block [<div class="card-block">] for card
    var card_block = document.createElement("DIV");
    card_block.className = 'card-block';
    

    //create row [<div class="row">] for card
    var div_row = document.createElement("DIV");
    div_row.className = 'row';


    //create columns [<div class='col]
    var col_picture = document.createElement("DIV");
    col_picture.className = 'col';
  
    var col_info = document.createElement("DIV");
    col_info.className = 'col';

    var col_link = document.createElement("DIV");
    col_link.className = 'col';
    

    //column 1: create div class for picture
    var div_Picture = document.createElement('DIV').className = 'card-text card_photo';


    //column 2: create classes for employee info 
    var h6_Name = document.createElement('H6').className = 'card-title';
    var p_Role = document.createElement('P').className = 'card-text';
    var p_Location = document.createElement('P').className = 'card-text';
    var p_Department = document.createElement('P').className = 'card-text';
    

    //column 3: create <a href> for csap link
    var a_tag = document.createElement('A').className = 'card-link align-middle';


    //column 3: add csap hyperlink to full name
    var link = "URL_PATH" + id;
    a_tag.setAttribute("href", link);


    //create text nodes from parameters
    var text_Picture = document.createTextNode(picture_src); //revise for img src instead of text
    
    var text_Name = document.createTextNode(fullName);
    var text_Role = document.createTextNode(role);
    var text_Location = document.createTextNode(location);
    var text_Department = document.createTextNode(department);

    var text_cardLink = document.createTextNode('>');
    
    
    //append text nodes to tags 
    div_Picture.appendChild(text_Picture);

    h6_Name.appendChild(text_Name);
    p_Role.appendChild(text_Role);
    p_Location.appendChild(text_Location);
    p_Department.appendChild(text_Department);

    a_tag.appendChild(text_cardLink);


    //append tags to columns
    col_picture.appendChild(div_Picture)
    
    col_info.appendChild(h6_Name);   
    col_info.appendChild(p_Role);   
    col_info.appendChild(p_Location);   
    col_info.appendChild(p_Department);

    col_link.appendChild(a_tag);


    //append columns to row
    div_row.appendChild(col_picture);
    div_row.appendChild(col_info);
    div_row.appendChild(col_link);


    //append row to card-block
    card_block.appendChild(div_row);


    //append card_block to card
    card_div.appendChild(card_block)
  }



</script>
