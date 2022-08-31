<?php

declare(strict_types=1);
class Lindy_38153 extends IPSModule
{
    public function Create()
    {
        $this->RequireParent('{3CFF0FD9-E306-41DB-9B5A-9D06D38576C3}');
        $this->RegisterPropertyInteger('UpdateInterval', 60);

        $this->RegisterTimer('Update', $this->ReadPropertyInteger('UpdateInterval') * 1000, 'LINDY_UpdateMapping($_IPS[\'TARGET\']);');
        $this->RegisterTimer('WatchdogTimer', $this->ReadPropertyInteger('UpdateInterval') * 2000, 'LINDY_WatchdogTimerElapsed($_IPS[\'TARGET\']);');

        $this->CreateVariableProfile();
        $this->Maintain();

        parent::Create();
    }

    public function ApplyChanges()
    {
        $this->SetTimerInterval('Update', $this->ReadPropertyInteger('UpdateInterval') * 1000);
        $this->Maintain();

        parent::ApplyChanges();
    }

    public function WatchdogTimerElapsed()
    {
        $this->SetValue('State', false);
        $this->SetTimerInterval('WatchdogTimer', 0);
    }

    private function WatchdogReset()
    {
        $this->SetTimerInterval('WatchdogTimer', $this->ReadPropertyInteger('UpdateInterval') * 2000);
        $this->SetValue('State', true);
    }

    public function ReceiveData($JSONString)
    {
        $data = json_decode($JSONString);
        $message = strtoupper(bin2hex(utf8_decode($data->Buffer)));
        $this->SendDebug('ReceiveData()', $message, 0);

        if (strlen($message)!=26) {
            $this->SendDebug('ReceiveData()', "Falsche Länge (".strlen($message) .")", 0);
            return;
        }

        // Zurücksetzen des WatchdogTimers bei Empfang einer Nachricht
        $this->WatchdogReset();

        if (substr($message, 0, 6)=="A55B02") { // Zuordnung der Ein- und Ausgänge
            $out =  intval(substr($message, 8, 2));
            $Input =  intval(substr($message, 12, 2));

            if ($out>0 && $out<9) {
                $this->SendDebug('ReceiveData()', "Ausgang ". $out . " ==> Eingang". $Input, 0);
                $this->SetValue('Output'.$out, $Input);
            }
        }
    }


    public function RequestAction($Ident, $Value)
    {
        $this->SendDebug('RequestAction()', "Ident ". $Ident . " ==> Value". $Value, 0);

        if (substr($Ident, 0, 6) == "Output") {
            $this->SetMapping(intval(substr($Ident, 6, 1)), $Value);
        }
    }


    public function SetMapping(int $output, int $input)
    {
        $cmd = "A55B0203"; // Port umschalten
        $cmd .= sprintf("%'.02d", $input);
        $cmd .= "00"; // Auffüllen
        $cmd .= sprintf("%'.02d", $output);
        $cmd .= "0000000000"; // Auffüllen
        $this->SendCommand($cmd);

        IPS_Sleep(200); // Wartezeit bis Eingang geändedrt wurde, da sonst noch der alte Wert abgerufen wird

        // Aktualisieren des Ausgangs
        $cmd  = "A55B0201".sprintf("%'.02d", $output)."00000000000000"; // Status für diesen Ausgang abfragen
        $this->SendCommand($cmd);
    }


    public function UpdateMapping()
    {
        for ($i = 1; $i <= 8; $i++) {
            $cmd  = "A55B02010".$i."00000000000000"; // Status für jeden Ausgang abfragen
            $this->SendCommand($cmd);
        }
    }


    public function SendCommand(string $command)
    {
        $this->SendDebug('SendCommand()', "Command: ".$command, 0);
        $command = str_replace(":", "", $command); 	// Doppelpunkte aus HEX-String entfernen
        $command = str_replace(" ", "", $command);	// Leerzeichen aus Hex-String entfernen

        $output='';
        $checksum = 0;
        $elements = explode("\n", trim(chunk_split($command, 2))); 	// Ein Array aus Blöcken mit je 2 HEX-Zeichen (8-Bit) generiern.
        foreach ($elements as $element) {
            $output.=chr(hexdec($element));			// Umwandlung von 2 HEX-Zeichen in ein Byte
            $checksum+= hexdec($element); 		// Addition der einzelenen Bytes für Checksumme
        }

        $output.=chr(256-$checksum%256); 			//Checksumme berechnen

        $this->SendDebug('SendCommand()', "Command mit Checksumme: ". strtoupper(bin2hex(($output))), 0);


        return $this->SendDataToParent(json_encode(['DataID' => '{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}', "Buffer" => (utf8_encode($output))]));
    }

    private function CreateVariableProfile()
    {
        $this->SendDebug('RegisterVariableProfiles()', 'RegisterVariableProfiles()', 0);

        if (!IPS_VariableProfileExists('LINDY_Online')) {
            IPS_CreateVariableProfile('LINDY_Online', 0);
            IPS_SetVariableProfileAssociation('LINDY_Online', 0, $this->Translate('Offline'), '', 0xFF0000);
            IPS_SetVariableProfileAssociation('LINDY_Online', 1, $this->Translate('Online'), '', 0x00FF00);
        }


        if (!IPS_VariableProfileExists('LINDY_Inputs')) {
            IPS_CreateVariableProfile('LINDY_Inputs', 1);
            for ($i = 1; $i <= 8; $i++) {
                IPS_SetVariableProfileAssociation('LINDY_Inputs', $i, $this->Translate('Input') . " " . $i, '', 0x00FF00);
            }
        }
    }

    private function Maintain()
    {
        $this->MaintainVariable('State', $this->Translate('State'), 0, 'LINDY_Online', 10, ($this->ReadPropertyInteger('UpdateInterval')!=0)); // Status ausblenden wenn UpdateIntervall/Watchdog 0 ist 

        for ($i = 1; $i <= 8; $i++) { // Ausgang 1...8 
            $this->MaintainVariable('Output'.$i, $this->Translate('Output')." ". $i, 1, 'LINDY_Inputs', $i, true);
            $this->EnableAction('Output'.$i);
        }
    }
}
