$(document).ready(function () {
    $("#departments-tab").on('click', function () {
        $("#addTeamBtn").removeClass('d-inline-block').addClass('d-none');
        $("#addTechnicanBtn").removeClass('d-inline-block').addClass('d-none');
    });

    $("#teams-tab").on('click', function () {
        $("#addTeamBtn").removeClass('d-none').addClass('d-inline-block');
        $("#addTechnicanBtn").removeClass('d-inline-block').addClass('d-none');
    });

    $("#technicians-tab").on('click', function () {
        $("#addTeamBtn").removeClass('d-inline-block').addClass('d-none');
        $("#addTechnicanBtn").removeClass('d-none').addClass('d-inline-block');
    });
});