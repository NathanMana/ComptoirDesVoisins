/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import '../css/app.css';

// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
// import $ from 'jquery';

let $ = require('jquery');

$(document).ready(function(){
    const h1 = $('h1').text();
    let cityInput;

    if(h1 === "Inscription"){
        cityInput = $('#registration_city');
    } else if (h1 === "Créer mon annonce"){
        cityInput = $('#advert_creation_city');
    } else if(h1 === "Profil"){
        cityInput = $('#profile_city');
    } else if(h1==="Création de mon annonce"){
        cityInput = $('#city1');
    }
    const apiUrl = "https://geo.api.gouv.fr/communes?nom=";
    const format = "&format=json";
    

    $(cityInput).on('blur', function(){
        let cityName = $(this).val();
        let url = apiUrl + cityName + format;
        fetch(url, {method: "get"}).then(response=>response.json()).then(results => {
            if(results.length){
                $('.error').hide();
                $.each(results, function(key, value){
                    $('tbody').append('<tr><td class="CityVal" alt="'+value.code+'">'+ value.nom +' ('+ value.codeDepartement +')</td></tr>');
                });
                $('tr td').click(function(){
                    let CityValue = $(this).text();
                    let CityCode = $(this).attr('alt');
                    if(h1 === "Créer mon annonce"){
                        $('#advert_creation_city').val(CityValue);
                        $('#advert_creation_code_city').val(CityCode);
                    } else if (h1 === "Inscription"){
                        $('#registration_city').val(CityValue);
                        $('#registration_code_city').val(CityCode);
                    } else if (h1 === "Profil"){
                        $('#profile_city').val(CityValue);
                        $('#profile_code_city').val(CityCode);
                    } else if (h1 === "Création de mon annonce"){
                        $(cityInput).val(CityValue);
                        $('#offer_creation_citiesDelivery').val(CityValue);
                        $('#offer_creation_code_cities').val(CityCode);
                    }
                    $('tbody').empty();
                })
            } else {
                if($(cityName).val()){
                    $('.error').text("Nous ne trouvons pas cette ville");
                } else {
                    $('.error').hide();
                }
            }
        });
    });

    $(cityInput).focus(function(){
        $('tbody').empty();
    });

    // let compteurCities = 1;
    // $('.add-city').click(function(){
    //     compteurCities++;
    //     $('.cities').append('<input type="text" class="city-in-cities" name="cities'+ compteurCities +'" id="city'+ compteurCities +'">')
    //     cityInput = $('#city'+compteurCities);
    // })
    
})
