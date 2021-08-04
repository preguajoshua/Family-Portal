<!DOCTYPE html>
<html>
<head>
    <title>Calendar</title>
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootswatch/3.3.6/cerulean/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.6.0/fullcalendar.min.css" rel="stylesheet">

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.6.0/fullcalendar.min.js"></script>
</head>
<body>

<div class="content">

    <div class="row">
        <div class="col-md-5 pull-left">
            <div><b style="color:#555555;margin:0;font-size:16px;">{% $client->LastName %}, {% $client->FirstName %}</b></div>
            <div><b>DOB:</b> {% date("M d, Y", strtotime($client->DOB)) %}</div>
        </div>

        <div class="col-md-5 text-right pull-right">
            <div><b>{% $provider->Name %}</b></div>
            <div>{% $provider->AddressLine1 %}</div>
            @if ($provider->showCountyDistrict)
            <div>{% $provider->CountyDistrict %}, {% $provider->AddressCity %}, {% $provider->AddressZipCode %}</div>
            @else
            <div>{% $provider->AddressCity %}, {% $provider->AddressStateCode %} {% $provider->AddressZipCode %}</div>
            @endif
            <div>Phone: {% formatPhone($provider->PhoneWork) %}</div>
            <div>Fax: {% formatPhone($provider->FaxNumber) %}</div>
        </div>
    </div>

    <div class="card medication-card">
        <div id="calendar"></div>

        <div style="margin-top:15px;">
            <span class="legend family"><i class="fa fa-square"></i> Family Event</span>
            <span class="legend agency"><i class="fa fa-square-o"></i> Client Schedule</span>
            <span class="legend completed"><i class="fa fa-square-o"></i> Completed</span>
        </div>
    </div>
</div>

<style>
    .legend {
        padding-right: 10px;
    }
    .legend.family i {
        color: #dddddd;
    }
    .legend.agency {
        color: #317eac;
    }
    .legend.completed {
        color: #4caf50;
    }
    #calendar h2 {
        font-size: 24px;
    }
    #calendar .fc-basic-view .fc-body .fc-row {
        min-height: 8em;
    }
    #calendar .fc-toolbar .fc-right {
        display: none !important;
    }
    #calendar .fc-event.fc-visit {
        background-color: transparent;
        border: 1px solid #317eac;
        color: #317eac;
        padding: 2px 5px;
    }
    #calendar .fc-event.fc-visit.fc-completed {
        border: 1px solid #4caf50;
        color: #4caf50;
    }
    #calendar .fc-event.fc-note {
        background-color: #e6e6e6;
        border: 0;
        color: #555;
        padding: 2px 5px;
    }
    #calendar .fc-day-grid-event .fc-content {
        white-space: normal;
    }
</style>
<script type="text/javascript">
$(document).ready(function() {

    function formatEventsData(eventData, StartDate, EndDate) {
        eventData.startDateObject = moment(StartDate, "YYYY-MM-DD HH:mm:ss");
        eventData.endDateObject = moment(EndDate, "YYYY-MM-DD HH:mm:ss");
        eventData.start = eventData.startDateObject.format("YYYY-MM-DD HH:mm:ss");
        eventData.end = eventData.endDateObject.format("YYYY-MM-DD HH:mm:ss");
        eventData.startTime = eventData.startDateObject.format("h:mma");
        eventData.endTime = eventData.endDateObject.format("h:mma");
        eventData.sameDay = eventData.startDateObject.isSame(eventData.endDateObject, "day");
        eventData.className += !eventData.sameDay ? " fa-diffday" : "";
        eventData.displayTime = eventData.startDateObject.format("ddd, MMMM D")+", "+eventData.startTime.replace(":00", "");
        eventData.displayTimePreview = eventData.startTime.replace(":00", "");
        if(!eventData.sameDay) {
            eventData.displayTime += " - "+eventData.endDateObject.format("ddd, MMMM D")+", "+eventData.endTime.replace(":00", "");
        } else if(eventData.startTime != eventData.endTime) {
            eventData.displayTime += " - "+eventData.endTime.replace(":00", "");
            eventData.displayTimePreview += " - "+eventData.endTime.replace(":00", "");
        }
        return eventData;
    }

    function capitalize(str) {
        return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
    }

    $("#calendar").fullCalendar({
        height: 800,
        contentHeight: "auto",
        header: {
            left: "title"
        },
        defaultDate: moment("{% $monthDate %}"),
        eventSources: [
        {
            events: function(start, end, timezone, callback) {
                callback({!! json_encode($events->toArray()) !!});
            },
            cache: true,
            eventDataTransform: function(eventData) {
                eventData.title = eventData.TaskName;
                eventData.className = "fc-visit "+(eventData.CompletedStatus ? "fc-completed"  : "fc-missed");
                eventData.allDay = eventData.IsAllDay;
                eventData.eventType = "visit";
                eventData.displayName = capitalize(eventData.CareGiverName);

                return formatEventsData(eventData, eventData.EventStartDate, eventData.EventEndDate);
            }
        },
        {
            events: function(start, end, timezone, callback) {
                callback({!! json_encode($notes) !!});
            },
            cache: true,
            eventDataTransform: function(eventData) {
                eventData.title = eventData.Title;
                eventData.className = "fc-note";
                eventData.eventType = "note";
                eventData.displayName = eventData.AuthorName;
                eventData.isNote = true;

                return formatEventsData(eventData, eventData.StartDate, eventData.EndDate);
            }
        }],
        eventRender: function(event, element) {
            element.find(".fc-content").html("\
            <div class=\"fc-time\">"+event.displayTimePreview+"</div>\
            <div class=\"fc-title\">"+(event.eventType == "visit" ? event.displayName : event.title)+"</div>");
        },
        viewRender: function(view, element) {
            $("#calendar > .fc-toolbar").find(".fc-today-button").addClass("btn btn-primary");
            $("#calendar > .fc-toolbar").find(".fc-createNote-button").addClass("btn btn-warning");
            $("#calendar .fc-button-group").find(".fc-button").addClass("btn");
            $("#calendar .fc-careHistory-button").addClass("btn");

            $(".fc-content-skeleton .fc-today", element).html("<span>"+$("#calendar .fc-today").text()+"</span>");
        }
    });
});
</script>

</body>
</html>
