$(document).ready(function () {
    
        // On Toggling Between Tickets View (default, on page load) and Tasks View
        $(document).on('change', '#toggleContainer', function (e) {
            let tasksViewEnabled = e.target.checked;
            // console.log(tasksViewEnabled);

            if (tasksViewEnabled) {
                $("#ticketView").fadeOut('fast', function () {
                    $("#taskView").fadeIn('fast');
                });
           
                $("#tasksTableFilterBtn").show();
                $("#ticketsTableFilterBtn").hide();
                $("#ticketsTableClearFilterBtn").hide();
                $("#logTicketBtn").hide();

                if ($("#allTasksTableTab").hasClass('active'))
                    $("#exportToExcelBtn").show();
            } else {
                $("#taskView").fadeOut('fast', function () {
                    $("#ticketView").fadeIn('fast');
                });
          
                $("#tasksTableFilterBtn").hide();
                $("#exportToExcelBtn").hide();
                $("#ticketsTableFilterBtn").show();
                $("#ticketsTableClearFilterBtn").show();
                $("#logTicketBtn").show();
            }
        });

    $("#allTasksLink").on('shown.bs.tab', function () {
        $("#exportToExcelBtn").show();
    });

    $("#allTasksLink").on('hidden.bs.tab', function () {
        $("#exportToExcelBtn").hide();
    });

    $("#attachment").change(function (ev) { 
        if (ev.target.files && ev.target.files[0]) {
            let fileName = ev.target.files[0].name;
            $('.tickets-custom-file-name').text(fileName);
        } else {
            $('.tickets-custom-file-name').text('No file selected');
        }
    });

    // $('body').on('click', '#ticketsAllTable tr td:not(.action-cell)', function (ev) {
    //     window.location.href = ticketDetailsURL;
    // });

    // $('body').on('click', '#myTasksTable tr td:not(.action-cell)', function (ev) {
    //     window.location.href = taskDetailsURL;
    // });

    // $('body').on('click', '#allTasksTable tr td:not(.action-cell)', function (ev) {
    //     window.location.href = taskDetailsURL;
    // });
});