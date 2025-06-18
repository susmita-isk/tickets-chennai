$(document).ready(function () {

    // On showing tasks tab, show buttons for filter/add
    $("#ticketTasksTab").on('shown.bs.tab', function () {
        $("#filterTicketsModalBtn, #addTaskModalBtn").show();
        $("#ticketLabelsContainer").hide();
    });
    $("#ticketTasksTab").on('hidden.bs.tab', function () {
        $("#filterTicketsModalBtn, #addTaskModalBtn").hide();
        $("#ticketLabelsContainer").show();
    });
});