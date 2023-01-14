<?php

class ShellyRGBW2Voute extends IPSModule
{
    public function Create()
    {
        //Never delete this line!
        parent::Create();

        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

        // properties
        $this->RegisterPropertyString('Topic', '');

        // variables
        $this->RegisterVariableBoolean("Connected", "Connected");
        $this->RegisterVariableFloat("Shelly_Power0", "Power L1", '~Watt.3680');
        $this->RegisterVariableFloat("Shelly_Power1", "Power L2", '~Watt.3680');
        $this->RegisterVariableFloat("Shelly_Power2", "Power L3", '~Watt.3680');
        $this->RegisterVariableFloat("Shelly_Total0", "Total L1", '~Electricity');
        $this->RegisterVariableFloat("Shelly_Total1", "Total L2", '~Electricity');
        $this->RegisterVariableFloat("Shelly_Total2", "Total L3", '~Electricity');
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
        if (empty($this->ReadPropertyString('Topic'))) return;

        $data = json_decode($JSONString);

        $Buffer = $data;

        if (fnmatch('*/online', $Buffer->Topic)) {
            $this->SetValue("Connected", $Buffer->Payload === 'true' ? true : false);
        }

        //Phase A
        if (fnmatch('*emeter/0/power', $Buffer->Topic)) {
            $this->SendDebug('Power L1 Payload', $Buffer->Payload, 0);
            $this->SetValue('Shelly_Power0', floatval($Buffer->Payload));
        }
        if (fnmatch('*emeter/0/total', $Buffer->Topic)) {
            $this->SendDebug('Total L1 Payload', $Buffer->Payload, 0);
            $this->SetValue('Shelly_Total0', floatval($Buffer->Payload) / 1000);
        }

        //Phase B
        if (fnmatch('*emeter/1/power', $Buffer->Topic)) {
            $this->SendDebug('Power L2 Payload', $Buffer->Payload, 0);
            $this->SetValue('Shelly_Power1', floatval($Buffer->Payload));
        }
        if (fnmatch('*emeter/1/total', $Buffer->Topic)) {
            $this->SendDebug('Total L2 Payload', $Buffer->Payload, 0);
            $this->SetValue('Shelly_Total1', floatval($Buffer->Payload) / 1000);
        }

        //Phase C
        if (fnmatch('*emeter/2/power', $Buffer->Topic)) {
            $this->SendDebug('Power L3 Payload', $Buffer->Payload, 0);
            $this->SetValue('Shelly_Power2', floatval($Buffer->Payload));
        }
        if (fnmatch('*emeter/2/total', $Buffer->Topic)) {
            $this->SendDebug('Total L3 Payload', $Buffer->Payload, 0);
            $this->SetValue('Shelly_Total2', floatval($Buffer->Payload) / 1000);
        }
    }
}