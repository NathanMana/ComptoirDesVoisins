import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import listPlugin from '@fullcalendar/list';

let $ = require('jquery');

$(document).ready(() => {

    $.ajax({
        url : "/calendrier/evenement",
        type : 'get',
        success : function(response){
            console.log(response);
            if(response.calendarModels){
                var calendarEl = document.getElementById('calendar');  
                var calendar = new Calendar(calendarEl, {
                    plugins: [ dayGridPlugin, listPlugin ],
                    header: {
                        left: 'prev',
                        center: 'title',
                        right: 'next'
                    },
                    defaultView: 'listMonth',
                    locale: 'fr',
                    events: response.calendarModels
                    
                });
                calendar.render();
            } else {
                return;
            }
        }
    });
    
})
