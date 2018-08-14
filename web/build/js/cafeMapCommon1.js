// 50.04821, 36.18862100000001
//49.953905, 36.33076249999999
var map;
//var autocomplete;
var infowindow;
var coordinates = {lat: 50.015, lng: 36.220};
var mainMarker;
var cafesArray = [];
var markers = [];
var mainPlaceId='';
var mainPlaceAddress='Стартовая точка';
var cafeToDbArray = [];
var radiusSearch = 500;


//*********************************************************************************************** ИНИЦИАЛИЗАЦИЯ КАРТЫ
function initMap() {
    //--  создание объекта карты с центром в Харькове
    map = new google.maps.Map(document.getElementById('map'), {
        center: coordinates,
        zoom: 15
    });
    //-- маркер местоположения - дефолтно - середина
    drawMainMarker();
    //-- получить и нарисовать кафе вокруг основного маркера
   // refreshMap();
    //-- активировать информационное окошко
    infowindow = new google.maps.InfoWindow();
    //-- обработка события по клику на карту - все маркеры удаляются, основной пеперисовываетсф в месте клика
    //-- и вокруг него рисуются кафешки
    google.maps.event.addListener(map, 'click', function(event) {
        if (mainMarker != undefined) {
            mainMarker.setMap(null);
            mainPlaceAddress='Стартовая точка';
            var latlng = event.latLng;
            var lat = latlng.lat();
            var lng = latlng.lng();
            coordinates = {lat: lat, lng: lng};
            cafesArray.length = 0;
            resetMarkers();
            drawMainMarker();
            refreshMap();
        }
    });

    //-- определение границ автокомплита - почему то не работает
    var defaultBounds = new google.maps.LatLngBounds(
        new google.maps.LatLng(50.04821, 36.18862100000001),
        new google.maps.LatLng(49.953905, 36.33076249999999));
    //-- инициализация автокомплита адреса
    var input = document.getElementById('searchTextField');
    var options = {
        bounds: defaultBounds,
        types: ['address']
    };
    autocomplete = new google.maps.places.Autocomplete(input, options);
    document.getElementById('searchTextField').placeholder = 'Введите адрес';
   // console.log('map is ready77111');

}

//*********************************************************************************************** НАРИСОВАТЬ ОСНОВНОЙ МАРКЕР
function drawMainMarker() {
    var infoWindowContent = '<div><strong>' + mainPlaceAddress + '</strong><br>';
    mainMarker = new google.maps.Marker({
        position: coordinates,
        map: map,
        title: 'Iam',
        draggable:true,
        clickable: true,
        animation: google.maps.Animation.DROP,
        visible: true ,
        icon: {
            path: google.maps.SymbolPath.CIRCLE,
            strokeColor: "green",
            scale: 8
        }
    });
    markers.push(mainMarker);
    if (mainPlaceId !== ''){
        var service = new google.maps.places.PlacesService(map);
        service.getDetails({
            placeId: mainPlaceId
        }, function(place, status) {
            if (status === google.maps.places.PlacesServiceStatus.OK) {
                infoWindowContent = '<div><strong>' + place.name + '</strong><br>'
                    + 'Place ID: ' + place.place_id + '<br>'
                    +  place.formatted_address + '</div>';
            }
        });
    }
    google.maps.event.addListener(mainMarker, 'click', function() {
        infowindow.setContent(infoWindowContent);
        infowindow.open(map, this);
    });
}

//*********************************************************************************************** УДАЛИТЬ ВСЕ МАРКЕРЫ
function resetMarkers() {
    for (var i = 0; i < markers.length; i++) {
        markers[i].setMap(null);
    }
    markers.length = 0;
}

//****************************************************************** ОБНОВЛЕНИЕ КАРТЫ  ******** ПОКАЗАТЬ БЛИЖАЙШИЕ КАФЕ
function refreshMap() {
    var service = new google.maps.places.PlacesService(map);
    service.nearbySearch({
        location: coordinates,
        radius: radiusSearch,
        type: ['cafe']
    }, callbackRefreshMap);
    map.setZoom(15);
    return(cafesArray);
}

//****************************************************************** ОБНОВЛЕНИЕ КАРТЫ  ******** ОБРАБОТКА РЕЗУЛЬТАТА refreshMap
function callbackRefreshMap(results, status) {
    if (status === google.maps.places.PlacesServiceStatus.OK) {
        var step1, step2, cafeData, url;
        cafeData =  createCafeMarkers(results);
        var data = encodeURIComponent(JSON.stringify(cafeData));
        url = "/map/" + data + "/map";
        step1 = $.ajax(url);
        step2 = step1.then(
            function (data) {
                var def = new $.Deferred();
                $("#cafeGrid").html(data);
                var switchButton=$("#showModeSwitch");
                $(switchButton)[0].dataset.action = 'show_db';
                $(switchButton)[0].innerText ='Показать список сохраненных кафе';
                def.resolve();
/*
                setTimeout(function () {
                    $("#cafeGrid").html(data);
                    def.resolve();
                },1000);
                */
                return def.promise();
            },
            function (err) {
                console.log('Завершился неудачей Ajax запрос');
            }
        );
    } else {
        alert('Не удалось получить данные Google Map Api: ' + status);
    }
}

function createCafeMarkers(results) {
    for (var i = 0; i < results.length; i++) {
        createMarker(results[i]);
        cafesArray.push({'id':results[i].id, 'name':results[i].name, 'address':results[i].vicinity,
            'lat':results[i].geometry.location.lat(), 'lng':results[i].geometry.location.lng(), 'addToDb':0 });
    }
    return cafesArray;
}

//****************************************************************** ОБНОВЛЕНИЕ КАРТЫ  ******** НАРИСОВАТЬ ОБЫЧНЫЙ МАРКЕР
function createMarker(place) {
    var content;
    var lat = place.geometry.location.lat();
    var lng = place.geometry.location.lng();
    var placeLoc = place.geometry.location;
    var marker = new google.maps.Marker({
        map: map,
        position: place.geometry.location
    });
    markers.push(marker);
    google.maps.event.addListener(marker, 'click', function() {
        content = '<b>' + place.name + '</b><br>' + place.id + '<br>'
            + lat + ', ' +  lng;
        infowindow.setContent(content);
        infowindow.open(map, this);
    });
}

//*********************************************************************************************** ПОИСК ПО АДРЕСУ
//-- если поиск успешный - все маркеры удаляются, адрес становится центром карты и вокруг него рисуются кафешки
function searchByAdress() {
    //-- проспект Науки, 14, Харків, Харківська область, Украина, 61000
    mainPlaceAddress = document.getElementById('searchTextField').value;
    mainPlaceId='';
    var place = autocomplete.getPlace();
    if (  place !==undefined && place.geometry ) {
        mainPlaceId = place.id;
        map.panTo(place.geometry.location);
        map.setZoom(15);
        coordinates = place.geometry.location;
        cafesArray.length = 0;
        mainMarker.setMap(null);
        resetMarkers();
        refreshMap();
        drawMainMarker();
    } else {
        var reply = {
            'text' : 'Поиск неудачный',
            'Google Map Api Reply' : place
        };
        objDump(reply);
        document.getElementById('searchTextField').value = null;
        document.getElementById('searchTextField').placeholder = 'Введите адрес';
    }
}

//-- при нажатии на кнопку показа кафе все маркеры удаляются, увеличивается зум и в центре карты на месте кафе ставится маркер
function centeredCafe(item, drawMarker) {
    coordinates = {lat: parseFloat(item.dataset.lat), lng: parseFloat(item.dataset.lng)};
    map.setZoom(16);
    map.setCenter(coordinates);
    resetMarkers();
    var marker = new google.maps.Marker({
        map: map,
        position:coordinates
    });
    markers.push(marker);
    google.maps.event.addListener(marker, 'click', function() {
        var name = $("#td_name_" + item.dataset.cafe_id);
        if (name.length == 0){
            name = $("#td_title_" + item.dataset.cafe_id);
        }
        content = '<b>' + $(name)[0].innerText + '</b><br>' + '<br>'
            + item.dataset.lat + ', ' +  item.dataset.lng;
        infowindow.setContent(content);
        infowindow.open(map, this);
    });
}

//********************************************************************************************************  FRONT-END

//--загрузка в cafeGrid грида кафе из бд или тех, что на карте bd/map
function loadCafeFrom(sourse) {
    var data;
    switch (sourse){
        case 'db':
            $.ajax({
                url: '/map/' + sourse,
                type: "POST",
                success: function(response){
                    $("#cafeGrid").html(response);
                },
                error: function (jqXHR, error, errorThrown) {
                    console.log( "error : " + error + " " +  errorThrown);
                    console.log(jqXHR);
                }
            });
            break;
        case 'map':
            data = encodeURIComponent(JSON.stringify(cafesArray));
            $.get("/map/" + data + "/" + sourse).done(function(response){
                $("#cafeGrid").html(response);
        });
            break;
    }

}

//-- обработка клика на кнопку открытия окна редактирования
function updateWindowOpen(item) {
    // console.log(item.dataset.action);
    if (item.dataset.action == 'to_open'){
        //*** - открыть окно редактирования
        //-- звкрыть все открытые окна
        $(".updateWindow").html('').hide();
        //-- поменять свой шеврон на открытый
        item.dataset.action = 'to_close';
        getInfo(item.dataset.cafe_id);
    } else{
        //-- звкрыть все открытые окна
        $(".updateWindow").html('').hide();
        //-- поменять свой шеврон на открытый
        item.dataset.action = 'to_open';
    }
}

//-- обработка клика на кнопку открытия окна редактирования
function viewWindowOpen(item) {
//-- звкрыть все открытые окна
    $(".infoWindow").html('').hide();
    if (item.dataset.action == 'to_open'){
        item.dataset.action = 'to_close';
        getInfoView(item);
    } else{
        item.dataset.action = 'to_open';
    }
}

//-- вывод формы редактирования в открывшееся окно
function getInfo(cafe_id) {
    $.ajax({
        url: '/map/' + cafe_id + '/info1',
        type: "POST",
        success: function(response){
            $("#updateWindow_" + cafe_id).show().html(response);
        },
        error: function (jqXHR, error, errorThrown) {
            errorHandler(jqXHR, error, errorThrown);
        }
    });
}

//-- вывод данных в открывшееся окно просмотра
function getInfoView(item) {
    $("#infoWindow_" + item.dataset.cafe_id).show();
    $.ajax({
        url: '/map/' + item.dataset.cafe_id + '/info',
        type: "POST",
        dataType: 'json',
        success: function(response){
            if (response['status']){
                var viewExample = $("#viewExample");
                var newView = viewExample.clone(false).show();
                $("#infoWindow_" + item.dataset.cafe_id).append(newView).show();
                $("#id").html(response['data']['id']);
                $("#google_place_id").html(response['data']['google_place_id']);
                $("#title").html(response['data']['title']);
                $("#address").html(response['data']['address']);
                $("#raiting").html(response['data']['raiting']);
                $("#review").html(response['data']['review']);
                $("#status").html(response['data']['status']);
                $("#latLng").html(response['data']['lat'] + ', ' + response['data']['lang']);
            } else {
                $("#infoWindow_" + item.dataset.cafe_id).html('Информация не найдена');
            }
        },
        error: function (jqXHR, error, errorThrown) {
            console.log( "error : " + error + " " +  errorThrown);
            console.log(jqXHR);
        }
    });
}

function updateCafe() {
    var formData = $('[name="xle_cafebundle_cafe"]').serializeArray();
    $.ajax({
        url: '/map/modify',
        data: formData,
        type: "POST",
        dataType: 'json',
        success: function(response){
            if (response['status']){
                $("#td_raiting_" + response['data']['id']).html(response['data']['raiting']);
                $("#td_status_" + response['data']['id']).html(response['data']['status']);
                //-- звкрыть все открытые окна
                $(".updateWindow").empty().hide();
            } else {
                objDump(response['data']);
            }
        },
        error: function (jqXHR, error, errorThrown) {
            errorHandler(jqXHR, error, errorThrown);
        }
    });
}

//-- удаление кафе
function deleteCafe(item) {
    if (confirm('Подтвердите удаление')){
        alert('delete - ' + item.dataset.cafe_id);
        $.ajax({
            url: '/map/' + item.dataset.cafe_id + '/delete',
            type: "DELETE",
            dataType: 'json',
            success: function(response){
                if (response['status']){
                    $("#tr_" + item.dataset.cafe_id).remove();
                    alert('Кафе удалено')
                } else {
                    alert(('Ошибка : ' + response['data'] ))
                }
            },
            error: function (jqXHR, error, errorThrown) {
                errorHandler(jqXHR, error, errorThrown);
            }
        });
    }
}

//-- добавить отмеченные кафе в БД
function addCafiesToDB() {
    var name, address;
    cafeToDbArray = [];
    $('input:checkbox:checked').each(function(){
        if (this.dataset.db.length == 0){
            name = $("#td_name_" + this.dataset.cafe_id)[0].innerText;
            address = $("#td_address_" + this.dataset.cafe_id)[0].innerText;
            cafeToDbArray.push({id : this.dataset.cafe_id,
                name : name, address:address,
                lat : this.dataset.lat, lng : this.dataset.lng});
        }
    });
    if (cafeToDbArray.length > 0){
        $.ajax({
            url: '/map/append',
            data: JSON.stringify(cafeToDbArray),
            type: "POST",
            dataType: 'json',
            success: function(response){
                objDump(response['data']);
                loadCafeFrom('db');
                var switchButton=$("#showModeSwitch");
                $(switchButton)[0].dataset.action = 'show_map';
                $(switchButton)[0].innerText ='Показать список кафе на карте';
                loadCafeFrom('db', cafesArray);
            },
            error: function (jqXHR, error, errorThrown) {
                console.log( "error : " + error + " " +  errorThrown);
                console.log(jqXHR);
            }
        });
    }
}

//-- обработка ответа 403
function errorHandler(jqXHR, error, errorThrown){
    console.log('Ошибка:');
    console.log(errorThrown);
    console.log(jqXHR['status']);
    if (jqXHR['status']==403){
        alert('Действие не возможно, необходимо войти в систему, как администратор.');
    }
}

//** обработка клика кнопки выбора режима просмотра
function switchDbMap(item) {
    console.log($(item)[0].innerText);
    if (item.dataset.action == 'show_map'){
        item.dataset.action = 'show_db';
        $(item)[0].innerText ='Показать список сохраненных кафе';
        loadCafeFrom('map', cafesArray);
    } else {
        item.dataset.action = 'show_map';
        $(item)[0].innerText = 'Показать список кафе на карте';
        loadCafeFrom('db', cafesArray);
    }
}

function objDump(object) {
    var out = "";
    if(object && typeof(object) == "object"){
        for (var i in object) {
            out += i + ": " + object[i] + "\n";
        }
    } else {
        out = object;
    }
    alert(out);
}



