<?php

class Shelly25 extends IPSModule
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
        $this->RegisterPropertyString('Topic', '');

        // variables
        $this->RegisterVariableBoolean("Connected", "Connected");
        $this->RegisterVariableInteger("Position", "Position", '~Shutter');
        $this->EnableAction("Position");
        $this->RegisterVariableInteger("Roller", "Roller", '~ShutterMoveStop');
        $this->EnableAction("Roller");
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
        if (fnmatch('*/roller/0/pos*', $Buffer->Topic)) {
            $this->SetValue('Position', intval($Buffer->Payload));
        }
        if (fnmatch('*/roller/0', $Buffer->Topic)) {
            switch ($Buffer->Payload) {
                case 'open':
                    $this->SetValue('Roller', 0);
                    break;
                case 'stop':
                    $this->SetValue('Roller', 2);
                    break;
                case 'close':
                    $this->SetValue('Roller', 4);
                    break;
                default:
                    break;
            }
        }
    }

    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'Shelly_Roller':
                switch ($Value) {
                    case 0:
                        $this->MoveUp();
                        break;
                    case 2:
                        $this->Stop();
                        break;
                    case 4:
                        $this->MoveDown();
                        break;
                    default:
                        break;
                }
            break;
            case 'Shelly_RollerPosition':
                $this->Move($Value);
                break;
            }
        }
    }

    private function MoveDown()
    {
        $Topic = '/roller/0/command';
        $Payload = 'close';
        $this->SendRequest($Topic, $Payload);
    }

    private function MoveUp()
    {
        $Topic = '/roller/0/command';
        $Payload = 'open';
        $this->SendRequest($Topic, $Payload);
    }

    private function Move($position)
    {
        $Topic = '/roller/0/command/pos';
        $Payload = strval($position);
        $this->SendRequest($Topic, $Payload);
    }

    private function Stop()
    {
        $Topic = '/roller/0/command';
        $Payload = 'stop';
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