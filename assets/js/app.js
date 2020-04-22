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

// $(document).ready(function(){
//     const apiUrl = "https://geo.api.gouv.fr/communes?codePostal=";
//     const format = "&format=json";

//     let code_postal = $('#registration_code_postal');
//     let city = $('#registration_city');

//     $(code_postal).on('blur', function(){
//         let code = $(this).val();
//         let url = apiUrl + code + format;
//         fetch(url, {method: "get"}).then(response=>response.json()).then(results => {
//             if(results.length){
//                 $('.error').hide();
//                 $.each(results, function(key, value){
//                     $(city).append('<option value="'+ value.nom+'">'+ value.nom +'</option>');
//                 });
//             } else {
//                 if($(code_postal).val()){
//                     $('.error').text("Ce code postal n'existe pas");
//                 } else {
//                     $('.error').hide();
//                 }
//             }
//         });
//     })
// })
