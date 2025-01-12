<?php
if (!isset($contexts)) {
    $contexts = array();
}
?>

<script>
    $(document).ready(function() {

        var relatedToDropdowns = <?php echo json_encode($related_to_dropdowns); ?>;

        var dropdowns = {};
        dropdowns.milestones_dropdown = <?php echo json_encode($milestones_dropdown); ?>;
        dropdowns.assign_to_dropdown = <?php echo json_encode($assign_to_dropdown); ?>;
        dropdowns.collaborators_dropdown = <?php echo json_encode($collaborators_dropdown); ?>;
        dropdowns.label_suggestions = <?php echo json_encode($label_suggestions); ?>;
        dropdowns.statuses_dropdown = <?php echo json_encode($statuses_dropdown); ?>;


        showHideRelatedToDropdowns = function(selectedContext) {
            var contexts = <?php echo json_encode($contexts); ?>;

            $.each(contexts, function(index, context) {
                var $element = $("#" + context + "-dropdown");
                var $dropdownElement = $("#" + context + "_id");

                if (selectedContext === context) {
                    $element.removeClass("hide");
                    $element.find(".task-context-options").addClass("validate-hidden").attr("data-rule-required", true);
                    if (context !== "project") { //define the project differntly since there is a change event. Define only once.

                        $dropdownElement.appDropdown({
                            list_data: relatedToDropdowns[context]
                        });

                    }
                } else {
                    $dropdownElement.val(""); //reset selected value
                    $element.addClass("hide");
                    $element.find(".task-context-options").removeClass("validate-hidden").removeAttr("data-rule-required");
                }
            });
        };



        function resetRequiredTaskModalDropdowns(url, context, reload_context) {

            $("#milestone_id").appDropdown("destroy");
            $("#milestone_id").hide();
            $("#assigned_to").appDropdown("destroy");
            $("#assigned_to").hide();
            $('#collaborators').appDropdown("destroy");
            $("#collaborators").hide();
            $('#project_labels').appDropdown("destroy");
            $("#project_labels").hide();
            $("#task_status_id").appDropdown("destroy");
            $("#task_status_id").hide();

            if (context && reload_context) {
                $("#" + context + "_id").appDropdown({
                    destroy: true
                });
            }

            appLoader.show({
                container: "#dropdown-apploader-section",
                zIndex: 1
            });
            $.ajax({
                url: url,
                dataType: "json",
                success: function(result) {

                    initializeTaskModalCommonDropdowns(result, true);
                    if (context && reload_context) {
                        $("#" + context + "_id").show().val("");
                        $("#" + context + "_id").appDropdown({
                            list_data: result[context + "s_dropdown"]
                        });
                    }

                    appLoader.hide();
                }
            });
        }

        function showRelatedDropdowns(context, reload_context) {

            var contextId = $("#" + context + "_id").val();
            if (context) {
                var findContext = reload_context ? 0 : 1;
                resetRequiredTaskModalDropdowns("<?php echo get_uri('tasks/get_dropdowns') ?>" + "/" + context + "/" + contextId + "/" + findContext, context, reload_context);
                if (context === "project") {
                    $("#milestones-dropdown").removeClass("hide");
                } else {
                    $("#milestones-dropdown").addClass("hide");
                }

                showHideRelatedToDropdowns(context);
            }
        }

        function showHideDropdowns(context, dropdowns) {
            if (context) {
                if (context === "project") {
                    $("#milestones-dropdown").removeClass("hide");
                } else {
                    $("#milestones-dropdown").addClass("hide");
                }
                showHideRelatedToDropdowns(context);
                initializeTaskModalCommonDropdowns(dropdowns);
            }
        }

        function initializeTaskModalCommonDropdowns(result, resetValue) {
            if (resetValue) {
                $("#milestone_id").show().val("");
                $("#assigned_to").show().val("");
                $("#collaborators").show().val("");
                $("#project_labels").show().val("");
                $("#task_status_id").show().val(result.statuses_dropdown[0].id);
            }

            $("#milestone_id").appDropdown({
                list_data: result.milestones_dropdown
            });

            $("#assigned_to").appDropdown({
                list_data: result.assign_to_dropdown
            });

            $("#collaborators").appDropdown({
                multiple: true,
                list_data: result.collaborators_dropdown
            });

            $("#project_labels").appDropdown({
                multiple: true,
                list_data: result.label_suggestions
            });

            $('#task_status_id').appDropdown({
                list_data: result.statuses_dropdown
            });
        }


        var context = $("#task-context").val();
        showHideDropdowns(context, dropdowns);

        $('#priority_id').appDropdown({
            list_data: <?php echo json_encode($priorities_dropdown); ?>
        });

        if ($("#task-context").is("select")) {
            $('#task-context').appDropdown({
                onChangeCallback: function(context, instance) {
                    showRelatedDropdowns(context, true);
                }
            });
        }

        if ($("#project_id").length) {
            // initialize project dropdown if there is only project dropdown available and context dropdown is not available
            $('#project_id').appDropdown({
                list_data: relatedToDropdowns[context],
                onChangeCallback: function(value, instance) {
                    showRelatedDropdowns("project", false);
                }
            });
        }

    });
</script>