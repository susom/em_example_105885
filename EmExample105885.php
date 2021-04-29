<?php
namespace Stanford\EmExample105885;

use REDCap;
require_once "emLoggerTrait.php";

class EmExample105885 extends \ExternalModules\AbstractExternalModule {

    use emLoggerTrait;

    public function redcap_save_record( $project_id, $record, $instrument, $event_id, $group_id = NULL, $survey_hash = NULL, $response_id = NULL, $repeat_instance = 1) {

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
}
