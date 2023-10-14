<?php

class Shelly3EM extends IPSModule
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
        $this->RegisterVariableBoolean("State", "State", '~Switch');
        $this->EnableAction("State");
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

        // Relay
        if (fnmatch('*/relay/0', $Buffer->Topic)) {
            $value = $Buffer->Payload;
            $this->SetValue("State", $value == 'on' ? true : false);
        }
    }

    public function RequestAction($Ident, $Value)
    {
        if($Ident === 'State') {
            $this->SwitchMode(0, $Value);
        }
    }

    private function SwitchMode(int $relay, bool $Value)
    {
        $Topic = 'relay/' . $relay . '/command';
        if ($Value) {
            $Payload = 'on';
        } else {
            $Payload = 'off';
        }
        $this->SendRequest($Topic, $Payload);
    }

    public function SendRequest(string $Ident, string $Value)
    {
        //MQTT Server
        $Server['DataID'] = '{043EA491-0325-4ADD-8FC2-A30C8EEB4D3F}';
        $Server['PacketType'] = 3;
        $Server['QualityOfService'] = 0;
        $Server['Retain'] = false;
        $Server['Topic'] = 'shellies/' . $this->ReadPropertyString('Topic') . '/' . $Ident;
        $Server['Payload'] = $Value;
        $ServerJSON = json_encode($Server, JSON_UNESCAPED_SLASHES);
        $resultServer = $this->SendDataToParent($ServerJSON);
    }
}