<?php

class MyZwave_melder extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();

        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

        // properties
        $this->RegisterPropertyString('Topic', '');

        // variables
        $this->RegisterVariableString("Alarm_6", "Alarm_Serverraum", "Alarm");
        $this->RegisterVariableInteger("Battery_6", "Battery_Serverraum", '~Battery.100');
        $this->RegisterVariableString("Alarm_7", "Alarm_Waschkeller", "Alarm");
        $this->RegisterVariableInteger("Battery_7", "Battery_Waschkeller", '~Battery.100');
        $this->RegisterVariableString("Alarm_8", "Alarm_Treppe", "Alarm");
        $this->RegisterVariableInteger("Battery_8", "Battery_Treppe", '~Battery.100');
        $this->RegisterVariableString("Alarm_9", "Alarm_Schlafzimmer", "Alarm");
        $this->RegisterVariableInteger("Battery_9", "Battery_Schlafzimmer", '~Battery.100');

    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();

        $topic = $this->ReadPropertyString('Topic');
        $this->SetReceiveDataFilter('.*' . $topic . '.*');
    }

    public function ReceiveData($JSONString)
    {
        $this->SendDebug('JSON', $JSONString, 0);
        if (empty($this->ReadPropertyString('Address'))) return;

        $data = json_decode($JSONString);

        $Buffer = $data;


        //Melder #6 Serverraum
        if (fnmatch('*ZZWayVDev_zway_6-0-113-1-2-A', $Buffer->Topic)) {
            $this->SendDebug('Alarm_6 Payload', $Buffer->Payload, 0);
            $this->SetValue('Alarm_6', strval($Buffer->Payload));
        }
        if (fnmatch('*ZWayVDev_zway_6-0-128', $Buffer->Topic)) {
            $this->SendDebug('Battery_6 Payload', $Buffer->Payload, 0);
            $this->SetValue('Battery_6', intval($Buffer->Payload));
        }

        //Melder #7 Waschkeller
        if (fnmatch('*ZZWayVDev_zway_7-0-113-1-2-A', $Buffer->Topic)) {
            $this->SendDebug('Alarm_7 Payload', $Buffer->Payload, 0);
            $this->SetValue('Alarm_7', strval($Buffer->Payload));
        }
        if (fnmatch('*ZWayVDev_zway_7-0-128', $Buffer->Topic)) {
            $this->SendDebug('Battery_7 Payload', $Buffer->Payload, 0);
            $this->SetValue('Battery_7', intval($Buffer->Payload));
        }

        //Melder #8 Waschkeller
        if (fnmatch('*ZZWayVDev_zway_8-0-113-1-2-A', $Buffer->Topic)) {
            $this->SendDebug('Alarm_8 Payload', $Buffer->Payload, 0);
            $this->SetValue('Alarm_8', strval($Buffer->Payload));
        }
        if (fnmatch('*ZWayVDev_zway_8-0-128', $Buffer->Topic)) {
            $this->SendDebug('Battery_8 Payload', $Buffer->Payload, 0);
            $this->SetValue('Battery_8', intval($Buffer->Payload));
        }

        //Melder #8 Waschkeller
        if (fnmatch('*ZZWayVDev_zway_9-0-113-1-2-A', $Buffer->Topic)) {
            $this->SendDebug('Alarm_9 Payload', $Buffer->Payload, 0);
            $this->SetValue('Alarm_9', strval($Buffer->Payload));
        }
        if (fnmatch('*ZWayVDev_zway_9-0-128', $Buffer->Topic)) {
            $this->SendDebug('Battery_9 Payload', $Buffer->Payload, 0);
            $this->SetValue('Battery_9', intval($Buffer->Payload));
        }

    }

}