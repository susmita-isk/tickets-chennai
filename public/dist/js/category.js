$(document).ready(function () {
    // $("#actionBtnsContainer button").hide();
    $("#filterCategoriesBtn").show();
    $("#addCategoryBtn").show();

    // On loading a particular tab, show only the action buttons for that tab (filter / add)
    $("#categoriesTabLink").on('shown.bs.tab', function (ev) {
        $("#actionBtnsContainer button").hide();
        $("#filterCategoriesBtn").show();
        $("#addCategoryBtn").show();
    });

    $("#subCategoriesTabLink").on('shown.bs.tab', function (ev) {
        $("#actionBtnsContainer button").hide();
        $("#filterSubCategoriesBtn").show();
        $("#addSubCategoryBtn").show();
    });
    $("#itemTypesTabLink").on('shown.bs.tab', function (ev) {
        $("#actionBtnsContainer button").hide();
        $("#filterItemTypesBtn").show();
        $("#addItemTypeBtn").show();
    });
    $("#itemsTabLink").on('shown.bs.tab', function (ev) {
        $("#actionBtnsContainer button").hide();
        $("#filterItemsBtn").show();
        $("#addItemBtn").show();
    });

    $("#subTaskTabLink").on('shown.bs.tab', function (ev) {
        $("#actionBtnsContainer button").hide();
        $("#filtersubcatTaskBtn").show();
        $("#addsubTaskBtn").show();
    });

    // On closing add category modal, remove validation errors and reset form
    $("#addCategoryModal").on('hidden.bs.modal', function () {
        $("#addCategoryForm").trigger('reset');
        $("#addCategoryForm").data('validator').resetForm();
        $("#addCategoryForm .form-control").removeClass('error').removeAttr('aria-invalid');
    });

    // On closing edit category modal, remove validation errors and reset form
    $("#editCategoryModal").on('hidden.bs.modal', function () {
        $("#editCategoryForm").trigger('reset');
        $("#editCategoryForm").data('validator').resetForm();
        $("#editCategoryForm .form-control").removeClass('error').removeAttr('aria-invalid');
    });

    // On closing add sub-category modal, remove validation errors and reset form
    $("#addSubCategoryModal").on('hidden.bs.modal', function () {
        $("#addSubCategoryForm").trigger('reset');
        $("#addSubCategoryForm").data('validator').resetForm();
        $("#addSubCategoryForm .form-control").removeClass('error').removeAttr('aria-invalid');
    });

    // On closing edit sub-category modal, remove validation errors and reset form
    $("#editSubCategoryModal").on('hidden.bs.modal', function () {
        $("#editSubCategoryForm").trigger('reset');
        $("#editSubCategoryForm").data('validator').resetForm();
        $("#editSubCategoryForm .form-control").removeClass('error').removeAttr('aria-invalid');
    });


    // Validate and submit Add Category From
    $("#addCategoryForm").validate({
        rules: {
            category_name: {
                required: true,
                maxlength: 50,
                minlength: 2
            }
        },
        errorElement: 'div',
        errorPlacement: function (error, element) {
            $(element).closest('.form-group').append(error);
        },
        submitHandler: function (form, ev) {
            $.ajax({
                url: form.action,
                type: form.method,
                data: $(form).serialize(),
                dataType: 'json',
                success: function (res) {
                    if (res.successCode == 1) {
                        toastr.success(res.message, '', { closeButton: true });
                        $("#addCategoryModal").modal('hide');
                        $('#categoriesTable').DataTable().ajax.reload(null, false);
                        loadCategories()
                    } else {
                        toastr.error(res.message, '', { closebutton: true });
                    }
                },
                error: function () {
                    toastr.error('Error adding. Please try again', '', { closebutton: true });
                }
            });
        }
    });

    
    // Validate and Submit Add Sub-Category Form
    $("#addSubCategoryForm").validate({
        rules: {
            sub_category_name: {
                required: true,
                minlength: 2,
                maxlength: 50
            },
            category: {
                required: true
            }
        },
        errorElement: 'div',
        errorPlacement: function (error, element) {
            $(element).closest('.form-group').append(error);
        },
        submitHandler: function (form, ev) {
            $.ajax({
                url: form.action,
                type: form.method,
                data: $(form).serialize(),
                dataType: 'json',
                success: function (res) {
                    if (res.successCode == 1) {
                        toastr.success(res.message, '', { closeButton: true });
                        $("#addSubCategoryModal").modal('hide');
                        $("#subCategoriesTable").DataTable().ajax.reload(null, false);
                    } else {
                        toastr.error(res.message, '', { closeButton: true });
                    }
                },
                error: function () {
                    toastr.error('Error Adding Sub-Category. Please try again', '', { closeButton: true });
                }
            })
        }
    });

   
}); // End of jQuery document.ready


function confirmCategoryActivationChange(el, status) {
    let category_id = $(el).data('id');

    // status: 0 = Deactivate, 1 = Activate
    if (status == 0) {
        $("#deactivateCategoryModal .confirmation-text").text(' Are you sure you want to Deactivate this Category?');
        $("#deactivateCategoryModal").modal('show');
        $("#confirmChangeCategoryActivationBtn").attr('data-id', category_id);
    } else {
        $("#deactivateCategoryModal .confirmation-text").text(' Are you sure you want to Activate this Category?');
        $("#deactivateCategoryModal").modal('show');
        $("#confirmChangeCategoryActivationBtn").attr('data-id', category_id);
    }
}

function confirmSubCategoryActivationChange(el, status) {
    let sub_category_id = $(el).data('id');

    // status: 0 = Deactivate, 1 = Activate
    if (status == 0) {
        $("#deactivateSubCategoryModal .confirmation-text").text(' Are you sure you want to Deactivate this Sub-Category?');
        $("#deactivateSubCategoryModal").modal('show');
        $("#changeSubCategActivationBtn").attr('data-id', sub_category_id);
    } else {
        $("#deactivateSubCategoryModal .confirmation-text").text(' Are you sure you want to Activate this Sub-Category?');
        $("#deactivateSubCategoryModal").modal('show');
        $("#changeSubCategActivationBtn").attr('data-id', sub_category_id);
    }
}

function changeCategoryActivation(el) {
    let category_id = $(el).attr('data-id');
    $.ajax({
        url: categoryActivationChangeURL,
        type: 'post',
        data: {
            _token: csrfToken,
            category_id: category_id
        },
        success: function (res) {
            if (res.successCode == 1) {
                toastr.success(res.message, '', { closeButton: true });
                $("#deactivateCategoryModal").modal('hide');
            } else {
                toastr.error(res.message, '', { closeButton: true });
            }
        },
        error: function () {
            toastr.error("Error updating. Please try again", '', { closeButton: true });
        }
    });
}

function changeSubCategoryActivation(el) {
    let sub_category_id = $(el).attr('data-id');
    $.ajax({
        url: subCategoryactivationChangeURL,
        type: 'post',
        data: {
            _token: csrfToken,
            sub_category_id: sub_category_id
        },
        success: function (res) {
            if (res.successCode == 1) {
                toastr.success(res.message, '', { closeButton: true });
                $("#deactivateSubCategoryModal").modal('hide');
            } else {
                toastr.error(res.message, '', { closeButton: true });
            }
        },
        error: function () {
            toastr.error("Error updating. Please try again", '', { closeButton: true });
        }
    });
}
