<?php

class ShellyPlugSPlus extends IPSModule
{
    protected function getChannelRelay(string $topic)
    {
        $ShellyTopic = explode('/', $topic);
        $LastKey = count($ShellyTopic) - 1;
        $relay = $ShellyTopic[$LastKey];
        return $relay;
    }

    public function Create()
    {
        //Never delete this line!
        parent::Create();

        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

        // properties
        $this->RegisterPropertyBoolean('RetainActuatorValues', false);
        $this->RegisterPropertyString('Topic', '');

        // variables
        $this->RegisterVariableBoolean("Connected", "Connected");
        $this->RegisterVariableBoolean("State1", "State1");
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

        $Buffer = json_decode($JSONString, true);

        $Payload = $Buffer['Payload'];
        if (array_key_exists('Topic', $Buffer)) {
            if (fnmatch('*/online', $Buffer['Topic'])) {
                $this->SetValue('Connected', $Payload == 'true');
            }
            if (fnmatch('*/events/rpc', $Buffer['Topic'])) {
                $Payload = json_decode($Payload, true);
                if (array_key_exists('params', $Payload)) {
                    
                    for ($i = 0; $i <= 3; $i++) {
                        $inputIndex = 'switch:' . $i;
                        if (array_key_exists($inputIndex, $Payload['params'])) {
                            $input = $Payload['params'][$inputIndex];
                            if (array_key_exists('output', $input)) {
                                $this->SetValue('State' . ($i + 1), $input['output']);
                            }
                        }
                    }
                }
            }
            if (fnmatch('*/actors/*', $Buffer['Topic'])) {
                $parts = explode('/', $Buffer['Topic']);
                $this->UpdateValue($parts[count($parts)-1], $value);
            }
            if (fnmatch('*/sensors/*', $Buffer['Topic'])) {
                $parts = explode('/', $Buffer['Topic']);
                $this->UpdateValue($parts[count($parts)-1], $value, true);
            }
        }
    }

    public function RequestAction($Ident, $Value)
    {
        //MQTT Server
        $Server['DataID'] = '{043EA491-0325-4ADD-8FC2-A30C8EEB4D3F}';
        $Server['PacketType'] = 3;
        $Server['QualityOfService'] = 0;
        $Server['Retain'] = $this->ReadPropertyBoolean('RetainActuatorValues');
        $Server['Topic'] = $this->ReadPropertyString('Topic') . '/actors/' . $Ident;
        $Server['Payload'] = json_encode($Value);
        $ServerJSON = json_encode($Server, JSON_UNESCAPED_SLASHES);
        $resultServer = $this->SendDataToParent($ServerJSON);
    }

    private function UpdateValue($key, $value, $readonly = true) {
        $type = gettype($value);
        if($type === 'integer' || $type === 'double') {
            $this->RegisterVariableFloat($key, $key);
        } else if($type === 'boolean') {
            $this->RegisterVariableBoolean($key, $key, $readonly ? '': '~Switch');
        } else {
            $this->RegisterVariableString($key, $key);
        }
        if(!$readonly) {
            $this->EnableAction($key);
        }
        $this->SetValue($key, $value);
    }
}