import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import listPlugin from '@fullcalendar/list';

let $ = require('jquery');

$(document).ready(() => {

    $.ajax({
        url : "/calendrier/evenement",
        type : 'get',
        success : function(response){
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
                    events: response.calendarModels,
                    eventRender: function(event){
                        var result = "<td class='fc-list-item-time fc-widget-content'><img style='width: 40px;' src='build/images/icon/camion.31200d24.svg' /></td><td class='fc-list-item-marker fc-widget-content'></td><td class='fc-list-item-title fc-widget-content'><a href='"+ event.event.url +"'>"+ event.event.title +"</a></td>";
                        $(event.el).html(result);
                    }
                });
                calendar.render();
            } else {
                return;
            }
        }
    });
    
    
})
