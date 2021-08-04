define([
    'app/container',
    'models/note',
    'moment',
    'bootbox',
    'toastr',
    'fullcalendar',
    'picker.date',
    'picker.time',
    'app/helper',
    'helpers/calendar',
    'app/validation',
    'can/view/stache',
    'can/model',
    'can/map/validations',
    'can/control/route',
    'can/route/pushstate',
], function (ioc, noteModel, moment, bootbox, toastr) {
    return ioc.Controller({
        defaults: {
            printUrl: '/pdf/calendar'
        }
    }, {
        provider: null,
        init: function () {
            this.provider = ioc.attr('provider');
            this.events = new can.List();
            this.render('views/calendar', this);
            this.calendar();

        },
        openCreateNoteDialog: function (input) {
            var self = this;

            var note = new noteModel({
                _StartDate: input ? input.format('MM/DD/YYYY') : moment().format('MM/DD/YYYY'),
                _StartTime: input ? input.format('h:mm A') : moment().format('h:mm A')
            });

            var form = $(can.view.render('views/calendar/createEventNote.stache', note));
            // var validator = new ioc.Validator(form.find('form'), note);

            // Create new dialog box
            bootbox.dialog({
                title: 'Create Event',
                backdrop: true,
                onEscape: true,
                size: 'medium',
                message: form,
                buttons: {
                    'cancel': {
                        label: 'Cancel'
                    },
                    'create': {
                        label: 'Create',
                        callback: function () {
                            // if(!validator.isValid()) {
                            // 	return false;
                            // }

                            note.save(function (note) {
                                ioc.app.reload();
                                bootbox.hideAll();
                            }, function (xhr) {
                                ioc.Helper.handleErrors(xhr.responseJSON.data);
                            });

                            return false;
                        }
                    }
                }
            }).init(self.dateTimePlugins);
        },
        openEditNoteDialog: function (element) {
            noteModel.findOne({
                id: element.data('id')
            }, can.proxy(function (note) {
                var form = $(can.view.render('views/calendar/createEventNote.stache', note));
                var validator = new ioc.Validator(form.find('form'), note);

                bootbox.dialog({
                    title: 'Edit Note',
                    backdrop: true,
                    onEscape: true,
                    size: 'medium',
                    message: form,
                    buttons: {
                        'cancel': {
                            label: 'Cancel'
                        },
                        'create': {
                            label: 'Save',
                            callback: function () {

                                //if (!validator.isValid()) {
                                //   return false;
                                // }
                                console.log(note);
                                note.save(function (note) {
                                    ioc.app.reload();
                                    bootbox.hideAll();
                                }, function (xhr) {
                                    ioc.Helper.handleErrors(xhr.responseJSON.data);
                                });

                                return false;
                            }
                        }
                    }
                }).init(this.dateTimePlugins);
            }, this));
        },
        dateTimePlugins: function () {
            // Handle the date and time inputs
            var $dateInput = $('.bootbox .date-input').pickadate({
                editable: true,
                min: new Date(),
                format: 'mm/dd/yyyy',
                container: '#date-picker'
            });

            var $datePicker = $dateInput.pickadate('picker');

            $('.bootbox .date-input').off('click focus');

            var datePickerClosure = function (e) {
                e.preventDefault();
                if ($datePicker.get('open')) {
                    $datePicker.close()
                } else {
                    $datePicker.open()
                }

                e.stopPropagation()
            }

            $('.bootbox .date-button').on('click', datePickerClosure);

            var $timeInput = $('.time-input').pickatime({
                editable: true,
                container: '#time-picker'
            });

            $('.bootbox .time-button').on('click', function (e) {
                e.preventDefault();
                if ($timePicker.get('open')) {
                    $timePicker.close()
                } else {
                    $timePicker.open()
                }

                e.stopPropagation()
            });

            var $timePicker = $timeInput.pickatime('picker');
        },
        '.btn-edit-delete click': function (element, event) {
            bootbox.confirm('Are you sure you want to delete?', function (result) {
                if (result) {
                    noteModel.findOne({
                        id: element.data('id')
                    }, function (note) {
                        note.destroy();
                        ioc.app.reload();
                    });
                }
            });
        },
        '.btn-edit-note click': function (element, event, b) {
            this.openEditNoteDialog(element);
        },
        calendar: function () {
            var self = this;

            $('#calendar').fullCalendar({
                height: 600,
                contentHeight: "auto",
                header: {
                    right: 'createNote today prev,next',
                    left: 'title'
                },
                customButtons: {
                    createNote: {
                        text: 'Create Event',
                        click: function () {
                            self.openCreateNoteDialog();
                        }
                    },
                    careHistory: {
                        text: '<i>test</i>',
                        click: function () { },
                        icon: 'list'
                    }
                },
                eventSources: [{
                    events: function (start, end, timezone, callback) {
                        $.ajax({
                            url: '/front/calendar/events',
                            dataType: 'json',
                            data: {
                                start: start.format('YYYY-MM-DD'),
                                end: end.format('YYYY-MM-DD')
                            },
                            success: function (events) {
                                $("#loading-box").hide();
                                $("#calendar").removeClass('invisible').fadeIn();
                                callback(events);
                                self.events.replace(self.events.concat(new can.List(events)));
                            },
                            error: function () {
                                self.manageDomApiIfApiFails();
                            }
                        });
                    },
                    cache: true,
                    eventDataTransform: function (eventData) {
                        eventData.title = eventData.TaskName;
                        eventData.className = 'fc-visit ' + (eventData.CompletedStatus ? 'fc-completed' : 'fc-missed');
                        eventData.completedClassName = (eventData.CompletedStatus) ? 'completed' : '';
                        eventData.allDay = eventData.IsAllDay;
                        eventData.eventType = 'visit'
                        eventData.displayName = ioc.Helper.capitalize(eventData.CareGiverName);

                        return self.formatEventsData(eventData, eventData.VisitStartTime, eventData.VisitEndTime);

                        self.events.push(data);

                        return data;
                    }
                }, {
                    events: function (start, end, timezone, callback) {
                        $.ajax({
                            url: '/front/notes',
                            dataType: 'json',
                            data: {
                                start: start.format('YYYY-MM-DD'),
                                end: end.format('YYYY-MM-DD')
                            },
                            success: function (events) {
                                callback(events);
                                self.events.replace(self.events.concat(new can.List(events)));
                            },
                            error: function () {
                                self.manageDomApiIfApiFails();
                            }
                        });
                    },
                    cache: true,
                    eventDataTransform: function (eventData) {
                        eventData.title = eventData.Title;
                        eventData.className = 'fc-note';
                        eventData.completedClassName = '';
                        eventData.eventType = 'note'
                        eventData.displayName = eventData.AuthorName;
                        eventData.isNote = true;

                        return self.formatEventsData(eventData, eventData.StartDate, eventData.EndDate);

                        self.pushEventData(data);

                        return data;
                    }
                }],
                // dayClick: function(date, jsEvent, view) {
                // 	if($('.popover').length != 0) {
                // 		return;
                // 	}
                // 	self.openCreateNoteDialog(date);
                // },
                eventRender: function (event, element) {
                    element.find('.fc-content').html('\
			    		<div class="fc-time">' + event.displayTimePreview + '</div>\
			    		<div class="fc-title">' + (event.eventType == 'visit' ? event.displayName : event.title) + '</div>');

                    var content = $('<div>');
                    content.append(self.popoverRender(event));

                    element.popover({
                        title: event.name,
                        placement: function (context, src) {
                            $(context).addClass(event.className[0]);
                            return 'auto top';
                        },
                        data: event,
                        html: true,
                        content: content.html()
                    });
                },
                viewRender: function (view, element) {
                    self.events.replace(new can.List());
                    console.log('calendar view render');
                    $('#calendar > .fc-toolbar').find('.fc-today-button').addClass('btn btn-primary');
                    $('#calendar > .fc-toolbar').find('.fc-createNote-button').addClass('btn btn-warning');
                    $('#calendar .fc-button-group').find('.fc-button').addClass('btn');
                    $('#calendar .fc-careHistory-button').addClass('btn');

                    $('.fc-content-skeleton .fc-today', element).html('<span>' + $('#calendar .fc-today').text() + '</span>');
                }
            });
        },
        formatEventsData: function (eventData, StartDate, EndDate) {
            eventData.startDateObject = moment(StartDate, 'YYYY-MM-DD HH:mm:ss');
            eventData.endDateObject = moment(EndDate, 'YYYY-MM-DD HH:mm:ss');
            eventData.start = eventData.startDateObject.format('YYYY-MM-DD HH:mm:ss');
            eventData.end = eventData.endDateObject.format('YYYY-MM-DD HH:mm:ss');
            eventData.startTime = eventData.startDateObject.format('h:mma');
            eventData.endTime = eventData.endDateObject.format('h:mma');
            eventData.sameDay = eventData.startDateObject.isSame(eventData.endDateObject, 'day');
            eventData.className += !eventData.sameDay ? ' fa-diffday' : '';
            eventData.displayTime = eventData.startDateObject.format('ddd, MMMM D') + ', ' + eventData.startTime.replace(':00', '');
            eventData.displayTimePreview = eventData.startTime.replace(':00', '');

            if (!eventData.sameDay) {
                eventData.displayTime += ' - ' + eventData.endDateObject.format('ddd, MMMM D') + ', ' + eventData.endTime.replace(':00', '');
                eventData.displayTimePreview += ' - ' + eventData.endTime.replace(':00', '');
            } else if (eventData.startTime != eventData.endTime) {
                eventData.displayTime += ' - ' + eventData.endTime.replace(':00', '');
                eventData.displayTimePreview += ' - ' + eventData.endTime.replace(':00', '');
            }
            return eventData;
        },
        popoverRender: can.view('views/calendar/popover'),
        getPDFUrl: function () {
            var fc = $('#calendar').fullCalendar('getView');
            var start = fc.start.format('YYYY-MM-DD'),
                end = fc.end.format('YYYY-MM-DD');

            var params = ["ticket=" + ioc.attr('token'), 'start=' + start, 'end=' + end];
            return this.options.printUrl + '?' + params.join('&');
        },
        print: function (context, element, event) {
            event.preventDefault();
            var icon = new ioc.Helper.iconLoader(element.find('i'));

            icon.start();

            var url = this.getPDFUrl();

            var iframe = new ioc.Helper.download(url, function () {
                ioc.Helper.callPrint(this.iframeId);
                icon.stop();
            });
        },
        download: function (context, element, event) {
            event.preventDefault();
            var icon = new ioc.Helper.iconLoader(element.find('i'));

            icon.start();

            var url = this.getPDFUrl() + '&download=1';

            var iframe = new ioc.Helper.download(url, function () {
                icon.stop();
            });
        },
        manageDomApiIfApiFails() {
            $('#preloader').fadeOut();
            $("#calendar").hide();
            $("#loading-box").hide();
            $("#error-container").removeClass('hidden').fadeIn();
        }
    });
});
