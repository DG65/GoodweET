<?php



/**
 * GoodweETTile
 *
 * HTML-Kachel für den GoodWe ET Wechselrichter.
 * Liest Variablen der GoodweET-Instanz und stellt sie als animierte
 * Energiefluss-Kachel dar. Steuerbuttons werden an die Quell-Instanz
 * weitergereicht (RequestAction).
 *
 * Pattern identisch zu TessieVehicleTile (DG65).
 */
class GoodweETTile extends IPSModule
{
    private const SOURCE_MODULE = '{1C4B7E2A-8F3D-5A9C-4E1B-7D2F9A3C6E8B}';

    private const WATCH_IDENTS = [
        'soc', 'work_mode', 'grid_mode', 'connected',
        'pv_total', 'ac_power', 'bat_total_pwr', 'meter_total',
        'bat1_pwr', 'bat1_mode', 'bat1_volt', 'bat1_soc',
        'bat2_pwr', 'bat2_mode', 'bat2_volt',
        'grid_l1_volt', 'grid_l1_freq',
        'temp_heatsink',
        'e_pv_day', 'e_sell_day', 'e_buy_day',
        'e_charge_day', 'e_disch_day', 'e_load_day',
        'ctl_work_mode', 'ctl_feed_enable', 'ctl_internet',
    ];

    private const ACTION_MAP = [
        'work_mode'    => 'ctl_work_mode',
        'feed_enable'  => 'ctl_feed_enable',
        'ems_power'    => 'ctl_ems_power',
        'internet'     => 'ctl_internet',
        'restart'      => 'ctl_restart',
    ];

    private const DEF_ACCENT     = 0xF5A623;
    private const DEF_BACKGROUND = -1;
    private const DEF_BOX        = -1;
    private const DEF_TEXT       = -1;
    private const DEF_TEXTMUTED  = -1;
    private const DEF_FONT       = 'system';
    private const DEF_SCALE      = 1.0;

    public function Create()
    {
        parent::Create();

        $this->RegisterPropertyInteger('SourceInstance', 0);
        $this->RegisterPropertyInteger('ColorAccent',     self::DEF_ACCENT);
        $this->RegisterPropertyInteger('ColorBackground', self::DEF_BACKGROUND);
        $this->RegisterPropertyInteger('ColorBox',        self::DEF_BOX);
        $this->RegisterPropertyInteger('ColorText',       self::DEF_TEXT);
        $this->RegisterPropertyInteger('ColorTextMuted',  self::DEF_TEXTMUTED);
        $this->RegisterPropertyString('FontFamily',       self::DEF_FONT);
        $this->RegisterPropertyFloat('FontScale',         self::DEF_SCALE);
        $this->RegisterPropertyBoolean('ShowControls',    true);

        $this->SetVisualizationType(1);
    }

    public function Destroy()
    {
        parent::Destroy();
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();
        $this->SetVisualizationType(1);

        foreach ($this->GetMessageList() as $senderID => $messages) {
            foreach ($messages as $msg) {
                if ($msg === VM_UPDATE) {
                    $this->UnregisterMessage($senderID, VM_UPDATE);
                }
            }
        }

        $src = $this->ResolveSource();
        if ($src > 0 && IPS_InstanceExists($src)) {
            foreach (self::WATCH_IDENTS as $ident) {
                $vid = @IPS_GetObjectIDByIdent($ident, $src);
                if ($vid && $vid > 0) {
                    $this->RegisterReference($vid);
                    $this->RegisterMessage($vid, VM_UPDATE);
                }
            }
            $this->SetStatus(102);
        } else {
            $this->SetStatus(201);
        }

        $this->UpdateVisualizationValue($this->BuildPayload());
    }

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        if ($Message === VM_UPDATE) {
            $this->UpdateVisualizationValue($this->BuildPayload());
        }
    }

    public function GetConfigurationForm()
    {
        return file_get_contents(__DIR__ . '/form.json');
    }

    public function RequestAction($Ident, $Value)
    {
        $src = $this->ResolveSource();
        if ($src <= 0) {
            return;
        }

        // Direkte Steuer-Aktionen
        if (isset(self::ACTION_MAP[$Ident])) {
            $targetIdent = self::ACTION_MAP[$Ident];
            $vid = @IPS_GetObjectIDByIdent($targetIdent, $src);
            if ($vid && $vid > 0) {
                @RequestAction($vid, $Value);
            }
            return;
        }

        // Numerische EMS-Vorgabe direkt als Int
        if ($Ident === 'ems_power_val') {
            $vid = @IPS_GetObjectIDByIdent('ctl_ems_power', $src);
            if ($vid && $vid > 0) {
                @RequestAction($vid, (int)$Value);
            }
        }
    }

    public function ResetStyle()
    {
        $id = $this->InstanceID;
        IPS_SetProperty($id, 'ColorAccent',     self::DEF_ACCENT);
        IPS_SetProperty($id, 'ColorBackground', self::DEF_BACKGROUND);
        IPS_SetProperty($id, 'ColorBox',        self::DEF_BOX);
        IPS_SetProperty($id, 'ColorText',       self::DEF_TEXT);
        IPS_SetProperty($id, 'ColorTextMuted',  self::DEF_TEXTMUTED);
        IPS_SetProperty($id, 'FontFamily',      self::DEF_FONT);
        IPS_SetProperty($id, 'FontScale',       self::DEF_SCALE);
        IPS_ApplyChanges($id);
        $this->ReloadForm();
    }

    public function GetVisualizationTile()
    {
        $html = file_get_contents(__DIR__ . '/module.html');
        $html .= '<script>handleMessage(' . json_encode($this->BuildPayload()) . ');</script>';
        return $html;
    }

    // -----------------------------------------------------------------------
    // Payload-Aufbau
    // -----------------------------------------------------------------------

    private function BuildPayload()
    {
        $style = [
            'accent'    => $this->ColorHex($this->ReadPropertyInteger('ColorAccent'), '#f5a623'),
            'bg'        => $this->ColorOrEmpty($this->ReadPropertyInteger('ColorBackground')),
            'box'       => $this->ColorOrEmpty($this->ReadPropertyInteger('ColorBox')),
            'text'      => $this->ColorOrEmpty($this->ReadPropertyInteger('ColorText')),
            'textmuted' => $this->ColorOrEmpty($this->ReadPropertyInteger('ColorTextMuted')),
            'font'      => $this->FontStack($this->ReadPropertyString('FontFamily')),
            'scale'     => $this->FontScaleValue(),
            'controls'  => $this->ReadPropertyBoolean('ShowControls'),
        ];

        $src = $this->ResolveSource();
        if ($src <= 0 || !IPS_InstanceExists($src)) {
            return json_encode(array_merge($style, [
                'ok'        => false,
                'stateLabel'=> 'Keine Datenquelle',
            ]));
        }

        $g = function($ident) use ($src) {
            $vid = @IPS_GetObjectIDByIdent($ident, $src);
            return ($vid && $vid > 0) ? GetValue($vid) : null;
        };

        $connected  = (bool)($g('connected') ?? false);
        $soc        = (float)($g('soc') ?? 0);
        $workMode   = (int)($g('work_mode') ?? 0);
        $pvTotal    = (float)($g('pv_total') ?? 0);
        $acPower    = (float)($g('ac_power') ?? 0);
        $batPwr     = (float)($g('bat_total_pwr') ?? 0);
        $meterPwr   = (float)($g('meter_total') ?? 0);
        $bat1Pwr    = (float)($g('bat1_pwr') ?? 0);
        $bat1Mode   = (int)($g('bat1_mode') ?? 0);
        $bat1Soc    = (float)($g('bat1_soc') ?? $soc);
        $bat2Pwr    = (float)($g('bat2_pwr') ?? 0);
        $bat2Mode   = (int)($g('bat2_mode') ?? 0);
        $gridVolt   = (float)($g('grid_l1_volt') ?? 0);
        $gridFreq   = (float)($g('grid_l1_freq') ?? 0);
        $tempHs     = $g('temp_heatsink');
        $ePvDay     = (float)($g('e_pv_day') ?? 0);
        $eSellDay   = (float)($g('e_sell_day') ?? 0);
        $eBuyDay    = (float)($g('e_buy_day') ?? 0);
        $eChargeDay = (float)($g('e_charge_day') ?? 0);
        $eDischDay  = (float)($g('e_disch_day') ?? 0);
        $eLoadDay   = (float)($g('e_load_day') ?? 0);

        // Haus-Verbrauch: PV - Netz - Batterie  (Bilanz)
        $housePwr = $pvTotal - $meterPwr - $batPwr;

        $workModeLabels = [
            0 => 'Selbstverbrauch', 1 => 'Inselbetrieb', 2 => 'Backup',
            3 => 'Wirtschaftlich',  4 => 'Peak-Shaving', 5 => 'Erw. SV',
        ];

        $payload = array_merge($style, [
            'ok'           => $connected,
            'stateLabel'   => $connected ? ($workModeLabels[$workMode] ?? 'Unbekannt') : 'Getrennt',
            'workMode'     => $workMode,
            'soc'          => round($soc, 0),
            'bat1Soc'      => round($bat1Soc, 0),
            'bat1Mode'     => $bat1Mode,
            'bat2Mode'     => $bat2Mode,
            'pvW'          => round($pvTotal),
            'batW'         => round($batPwr),
            'gridW'        => round($meterPwr),
            'houseW'       => round(max(0, $housePwr)),
            'bat1W'        => round($bat1Pwr),
            'bat2W'        => round($bat2Pwr),
            'gridVolt'     => round($gridVolt, 1),
            'gridFreq'     => round($gridFreq, 2),
            'tempHs'       => ($tempHs !== null) ? round((float)$tempHs, 1) : null,
            'ePvDay'       => round($ePvDay, 2),
            'eSellDay'     => round($eSellDay, 2),
            'eBuyDay'      => round($eBuyDay, 2),
            'eChargeDay'   => round($eChargeDay, 2),
            'eDischDay'    => round($eDischDay, 2),
            'eLoadDay'     => round($eLoadDay, 2),
            'ctlInternet'  => (bool)($g('ctl_internet') ?? true),
        ]);

        return json_encode($payload);
    }

    // -----------------------------------------------------------------------
    // Hilfsfunktionen (identisch mit TessieVehicleTile)
    // -----------------------------------------------------------------------

    private function ResolveSource()
    {
        return (int)$this->ReadPropertyInteger('SourceInstance');
    }

    private function ColorHex(int $color, string $fallback)
    {
        if ($color < 0) {
            return $fallback;
        }
        return sprintf('#%06x', $color);
    }

    private function ColorOrEmpty(int $color)
    {
        return $color < 0 ? '' : sprintf('#%06x', $color);
    }

    private function FontStack(string $family)
    {
        if ($family === 'system' || $family === '') {
            return '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
        }
        return $family;
    }

    private function FontScaleValue()
    {
        $v = (float)$this->ReadPropertyFloat('FontScale');
        return ($v > 0 && $v <= 3.0) ? $v : 1.0;
    }
}
