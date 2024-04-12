<?php

class ShellyBluRuuvi extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();

        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

        // properties
        $this->RegisterPropertyString('Topic', '');

        // variables
        $this->RegisterVariableFloat("Alarm", "Alarm", "~Alert");
        $this->RegisterVariableFloat("Battery", "Battery", '~Battery.100');

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

        $Buffer = json_decode($JSONString, true);
        $Payload = json_decode($Buffer['Payload'], true);

        if(isset($Payload['temp'])) {
            $this->SetValue('Temperature', $Payload['temp']);
        }
        if(isset($Payload['humidity'])) {
            $this->SetValue('Humidity', $Payload['humidity']);
        }
        if(isset($Payload['batt'])) {
            $this->SetValue('Battery', $Payload['batt']);
        }
    }

}