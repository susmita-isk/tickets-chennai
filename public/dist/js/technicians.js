$(document).ready(function () {
    // On selecting Admin or Support Desk in New Technician, show options to
    // create login for them
    $("#userRole").change(function () {
        if ($("#userRole").val() == 'Support Desk' || $("#userRole").val() == 'Admin') {
            $("#newUserCredentialsRow").removeClass('d-none').addClass('d-flex');
            $("#teamNameRow").removeClass('d-flex').addClass('d-none');
        } else {
            $("#newUserCredentialsRow").removeClass('d-flex').addClass('d-none');
            $("#teamNameRow").removeClass('d-none').addClass('d-flex');
        }
    })  
});  // End of jQuery document.ready

