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

    const apiUrl = "https://geo.api.gouv.fr/communes?nom=";
    const format = "&format=json";


    if(h1 === "Inscription"){
        cityInput = $('#registration_city');
    } else if (h1 === "Profil") {
        cityInput = $('#profile_city');
    } else if (h1 === "Création de ma demande") {
        cityInput = $('#advert_creation_city');
    } else if (h1 === "Création de ma course"){
        cityInput = $('#offer_creation_citiesDelivery');
    }

    let timeout = null;


    $(cityInput).keyup(function(){
        clearTimeout(timeout);
        $('.proposition').empty();
        
        timeout = setTimeout(() => {

            let cityName = $(cityInput).val();
            let url = apiUrl + cityName + format;

            if(cityName != "" && cityName.length > 1){
                fetch(url, {method: "get"}).then(response=>response.json()).then(results => {
                    if(results.length > 0){

                        $('.proposition').append('<table><tbody>');
                        $.each(results.slice(0,5), function(key, value){
                            $('tbody').append('<tr class="CityVal"><td alt="'+value.code+'">'+ value.nom +' ('+ value.codeDepartement +')</td></tr>');
                        });
                        $('.proposition').append('</tbody></table>');

                        $('.CityVal').click(function(){

                            let CityValue = $(this).children().text();
                            let CityCode = $(this).children().attr('alt');
        
                            if(h1 === "Profil"){
                                $('#profile_city').val(CityValue);
                                $('#profile_codeCity').val(CityCode);
                            } else if (h1 === "Inscription") {
                                $('#registration_city').val(CityValue);
                                $('#registration_codeCity').val(CityCode);
                            } else if (h1 === "Création de ma demande"){
                                $('#advert_creation_city').val(CityValue);
                                $('#advert_creation_codeCity').val(CityCode);
                            } else if (h1 === "Création de ma course"){
                                $('#offer_creation_citiesDelivery').val(CityValue);
                                $('#offer_creation_codeCities').val(CityCode);
                            }            
                            $('.proposition').empty();
                        });

                    } else {
                        $('.proposition').append('<table><tbody><tr><td class="error">Nous ne trouvons pas cette ville</td></tr></table></tbody>');
                    }
                });
            }
        }, 500);
    });

    $('#registration_password').keyup((e) => {
        console.log("hlkjsdfl");
        let condition = document.getElementById('condition');
        if(e.target.value.length >= 8){
            condition.style.color = "green";
        } else {
            condition.style.color = null;
        }
    });

    $(cityInput).focus(function(){
        $('.proposition').empty();
    });

    let burger = document.querySelector('.burger');
    let icon = document.querySelector('.icon');
    let layout = document.querySelector('.mobile-layout');
    let menu = document.querySelector('.menu-mobile');
    let links = document.querySelectorAll('.menu-mobile__link');
    let nav = document.querySelector('.menu');
    let isOpen = true;

    burger.addEventListener('click', () => {
        isOpen ? burger.classList.add('active') : burger.classList.remove('active');
        isOpen ? layout.classList.add('active') : layout.classList.remove('active');
        isOpen ? nav.classList.add('active') : nav.classList.remove('active');
        if (isOpen) {
            setTimeout(() => {
                links[0].classList.add('active');
                setTimeout(() => {
                    links[1].classList.add('active');
                    setTimeout(() => {
                        links[2].classList.add('active');
                        setTimeout(() => {
                            links[3].classList.add('active');
                            setTimeout(() => {
                                links[4].classList.add('active');
                            }, 130);
                        }, 110);
                    }, 90);
                }, 70);
            }, 50);
        } else {
            setTimeout(() => {
                links[4].classList.remove('active');
                setTimeout(() => {
                    links[3].classList.remove('active');
                    setTimeout(() => {
                        links[2].classList.remove('active');
                        setTimeout(() => {
                            links[1].classList.remove('active');
                            setTimeout(() => {
                                links[0].classList.remove('active');
                            }, 130);
                        }, 110);
                    }, 90);
                }, 70);
            }, 50);
        }
        isOpen = !isOpen;
    });

})
