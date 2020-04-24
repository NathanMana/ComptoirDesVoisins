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

//     let city = $('#city');

//     $(city).on('blur', function(){
//         let cityName = $(this).val();
//         let url = apiUrl + cityName + format;
//         fetch(url, {method: "get"}).then(response=>response.json()).then(results => {
//             if(results.length){
//                 console.log(results);
//                 $('.error').hide();
//                 $.each(results, function(key, value){
//                     $('tbody').append('<tr>'+ value.nom +'</tr>');
//                 });
//             } else {
//                 if($(cityName).val()){
//                     $('.error').text("Nous ne trouvons pas d'annonce provenant de cette ville");
//                 } else {
//                     $('.error').hide();
//                 }
//             }
//         });
//     })
// })
