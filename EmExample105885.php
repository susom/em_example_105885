<?php
namespace Stanford\EmExample105885;

use REDCap;
require_once "emLoggerTrait.php";

class EmExample105885 extends \ExternalModules\AbstractExternalModule {

    use emLoggerTrait;

    public function ugly_method($record,$instrument,$event_id) {
        $updateValues = False;

        $ts = date("Y-m-d H:i");  // Todays date to use for setting

        $data = REDCap::getData("array", $record);

        $this->emDebug($data);
        $updateData = array();  // update array

        switch ($instrument) {
            case 'form_31_interim_health_care_contact':
                $this->emDebug("form_31_interim_health_care_contact");
                $updateValues = True;
                $updateData[$record][$event_id]['j31submitdat'] = $ts;
                break;

            case 'form_8_medical_history':
                $this->emDebug("form_8_medical_history");
                $updateValues = True;
                $updateData[$record][$event_id]['j8submitdat'] = $ts;
                break;

            default:
                // do nothing
                break;
        }

        if ($updateValues) {  // value changes so update
            $parameters = [
                'dataFormat' => 'array',
                'overwriteBehavior' => 'overwrite',
                'data' => $updateData
            ];

            $q = REDCap::saveData($parameters);
            if (!empty($q['errors'])) {
                $this->emError("Invalid save", $q, $parameters);
            }
        }
    }


    public function redcap_save_record( $project_id, $record, $instrument, $event_id, $group_id = NULL, $survey_hash = NULL, $response_id = NULL, $repeat_instance = 1) {
        $this->ugly_method($record,$instrument,$event_id);
        // or maybe try this

        $this->better_method($record,$instrument,$event_id);
    }


    /**
     * This might be a more traditional way to do this where you configure it with parameters so it
     * is more adaptable and can be used with other projects
     * @param $record
     * @param $instrument
     * @param $event_id
     */
    public function better_method($record, $instrument, $event_id) {
        $instances = $this->getSubSettings('instances');
        foreach ($instances as $i => $instance) {
            $logic = $instance['action-logic'];
            $dest_event_id = empty($instance['dest-event']) ? $event_id : $instance['dest-event'];
            $dest_field_name = $instance['dest-field'];

            // Confirm Logic is Set
            if (empty($logic)) {
                $this->emError("Required logic is missing!");
                continue;
            }

            // Evaluate logic:
            $eval = REDCap::evaluateLogic($logic, $this->getProjectId(), $record, $event_id);
            if (!$eval) {
                $this->emDebug("$i: logic false - skip");
                continue;
            }

            // Confirm Dest Field is Set
            if (empty($dest_field_name)) {
                $this->emError("Required dest field is missing!");
                continue;
            }

            $this->emDebug("Found match with instance $i and $logic for $dest_event_id / $dest_field_name");
            $parameters = [
                'dataFormat' => 'array',
                'data' => [$record => [$dest_event_id => [$dest_field_name = date("Y-m-d H:i")]]]
            ];

            $q = REDCap::saveData($parameters);
            if (!empty($q['errors'])) {
                $this->emError("Invalid save", $q, $parameters);
            }
        }
    }
}
