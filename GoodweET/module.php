<?php

// ---------------------------------------------------------------------------
// GoodweRegisterMap — alle Register-Konstanten und Variablen-Definitionen
// Konsolidiert 2026-06-10 (REGISTER_AUDIT.md): Steuerbank 47505/47511/47512,
// SOC/SOH aus BMS, Inselstatus, korrigierte Quellen.
// ---------------------------------------------------------------------------

class GoodweRegisterMap
{
    // ── Steuerregister (maßgebliche Live-Bank) ──────────────────────────
    const REG_WORK_MODE         = 47000;  // grobe Betriebsart (0-5)
    const REG_EMS_ENABLE        = 47505;  // 2 = EMS-Steuerung aktiv
    const REG_FEED_POWER_ENABLE = 47509;  // Export Ja/Nein
    const REG_FEED_POWER_LIMIT  = 47510;  // Export-Limit (W, U16)
    const REG_EMS_POWER_MODE    = 47511;  // EMS Leistungsmodus (Enum 0-12)
    const REG_EMS_POWER_SET     = 47512;  // EMS Leistungseinstellung (W, U16, max 34500)
    const REG_SOC_MIN           = 45356;  // Bat1 Min SOC ONLINE
    const REG_INTERNET_MODE     = 47017;  // 0=mit Internet, 1=ohne Internet
    const REG_RESTART           = 45220;

    // EMS-Leistungssollwert max für GW29.9k-ET: 50 A ≈ 34641 W, gedeckelt
    // (Register-Doku nennt fälschlich 10000 — gilt nur für kleine WR)
    const EMS_POWER_MAX         = 34500;

    // ── Intents (vendor-neutrale Nature, vom EMS aufgerufen) ────────────
    const INTENT_AUTO        = 0;
    const INTENT_PV_SELFUSE  = 1;
    const INTENT_GRID_CHARGE = 2;
    const INTENT_DISCHARGE   = 3;
    const INTENT_EXPORT      = 4;
    const INTENT_STANDBY     = 5;

    // Intent → EMS-Leistungsmodus (Register 47511)
    const INTENT_TO_MODE = [
        0 => 1,   // AUTO        -> Automatik
        1 => 2,   // PV_SELFUSE  -> Laden-Solar
        2 => 4,   // GRID_CHARGE -> AC-Import
        3 => 3,   // DISCHARGE   -> Entladen+Solar
        4 => 5,   // EXPORT      -> AC-Export
        5 => 8,   // STANDBY     -> Bereitschaft
    ];

    const WORK_MODES = [
        0 => 'Selbstverbrauch',
        1 => 'Inselbetrieb',
        2 => 'Backup',
        3 => 'Wirtschaftlich',
        4 => 'Peak-Shaving',
        5 => 'Erw. Selbstverbrauch',
    ];

    // EMS Leistungsmodus (Register 47511) — aus EMS.json
    const EMS_MODES = [
        0  => 'Gestoppt',
        1  => 'Automatik',
        2  => 'Laden - Solar',
        3  => 'Entladen + Solar',
        4  => 'AC - Import',
        5  => 'AC - Export',
        6  => 'Energiesparen',
        7  => 'Inselbetrieb',
        8  => 'Batterie - Bereitschaft',
        9  => 'Stromeinkauf',
        10 => 'Stromverkauf',
        11 => 'Batterie - Laden',
        12 => 'Batterie - Entladen',
    ];

    const BAT_MODES = [
        0 => 'No Battery',
        1 => 'Standby',
        2 => 'entlädt',
        3 => 'lädt',
    ];

    const GRID_MODES = [
        0  => 'Warten',
        1  => 'Einspeisung',
        2  => 'Einspeisung: Limit',
        3  => 'Einspeisung: Entsätt.',
        4  => 'Einspeisung: PV-Limit',
        5  => 'Einspeisung: Reaktiv',
        6  => 'Einspeisung: Blindl.',
        7  => 'Einspeisung: Absch.',
        8  => 'Einspeisung: PV-Opt.',
        9  => 'Einspeisung: ECO',
        10 => 'Fehler: HW-Schutz',
        11 => 'Fehler',
        17 => 'Bypass',
        18 => 'Inselbetrieb',
    ];

    // [ident, caption, type(F/I/B/S), profile, scaleFactor, archive, group, reg]
    const VARS_BASE = [
        ['soc',           'SOC',                'F', '~Battery.100',      1, true,  'basis', 'BMS Ø'],
        ['work_mode',     'Betriebsmodus',       'I', 'GoodweET.WorkMode', 1, true,  'basis', 'RW 47000'],
        ['grid_mode',     'Netzmodus',           'I', 'GoodweET.GridMode', 1, false, 'basis', 'DSP 35136'],
        ['island',        'Netzgetrennt (Insel)', 'B', '~Alert',           1, true,  'basis', 'calc'],
        ['pv_total',      'PV Gesamtleistung',   'F', 'GoodweET.Watt',     1, true,  'basis', 'DSP 35301'],
        ['ac_power',      'AC Wirkleistung',     'F', 'GoodweET.Watt',     1, true,  'basis', 'DSP 35139'],
        ['bat_total_pwr', 'Bat. Gesamtleistg.',  'F', 'GoodweET.Watt',     1, true,  'basis', 'Σ Bat1+Bat2'],
        ['meter_total',   'Netz Leistung',       'F', 'GoodweET.Watt',     1, true,  'basis', 'SM 36025'],
        ['bat_charge_max_w',   'Bat. max. Ladeleistung',  'F', 'GoodweET.Watt', 1, false, 'basis', 'BMS calc'],
        ['bat_discharge_max_w','Bat. max. Entladeleistung','F','GoodweET.Watt', 1, false, 'basis', 'BMS calc'],
        ['connected',     'Verbindung',          'B', '~Alert.Reversed',   1, false, 'basis', ''],
    ];

    // [ident, caption, type, profile, sf, archive, group, reg, mppt]
    const VARS_PV = [
        ['pv1_voltage', 'PV1 Spannung', 'F', 'GoodweET.Volt',   10, false, 'pv', 'DSP 35103', 1],
        ['pv1_current', 'PV1 Strom',    'F', 'GoodweET.Ampere', 10, false, 'pv', 'DSP 35104', 1],
        ['pv1_power',   'PV1 Leistung', 'F', 'GoodweET.Watt',    1, true,  'pv', 'DSP 35105', 1],
        ['pv2_voltage', 'PV2 Spannung', 'F', 'GoodweET.Volt',   10, false, 'pv', 'DSP 35107', 2],
        ['pv2_current', 'PV2 Strom',    'F', 'GoodweET.Ampere', 10, false, 'pv', 'DSP 35108', 2],
        ['pv2_power',   'PV2 Leistung', 'F', 'GoodweET.Watt',    1, true,  'pv', 'DSP 35109', 2],
        ['pv3_voltage', 'PV3 Spannung', 'F', 'GoodweET.Volt',   10, false, 'pv', 'DSP 35111', 3],
        ['pv3_current', 'PV3 Strom',    'F', 'GoodweET.Ampere', 10, false, 'pv', 'DSP 35112', 3],
        ['pv3_power',   'PV3 Leistung', 'F', 'GoodweET.Watt',    1, true,  'pv', 'DSP 35113', 3],
        ['pv4_voltage', 'PV4 Spannung', 'F', 'GoodweET.Volt',   10, false, 'pv', 'DSP 35115', 4],
        ['pv4_current', 'PV4 Strom',    'F', 'GoodweET.Ampere', 10, false, 'pv', 'DSP 35116', 4],
        ['pv4_power',   'PV4 Leistung', 'F', 'GoodweET.Watt',    1, true,  'pv', 'DSP 35117', 4],
        ['pv5_voltage', 'PV5 Spannung', 'F', 'GoodweET.Volt',   10, false, 'pv', 'DSP 35304', 5],
        ['pv5_current', 'PV5 Strom',    'F', 'GoodweET.Ampere', 10, false, 'pv', 'DSP 35305', 5],
        ['pv5_power',   'PV5 Leistung', 'F', 'GoodweET.Watt',    1, true,  'pv', 'DSP 35341', 5],
        ['pv6_voltage', 'PV6 Spannung', 'F', 'GoodweET.Volt',   10, false, 'pv', 'DSP 35306', 6],
        ['pv6_current', 'PV6 Strom',    'F', 'GoodweET.Ampere', 10, false, 'pv', 'DSP 35307', 6],
        ['pv6_power',   'PV6 Leistung', 'F', 'GoodweET.Watt',    1, true,  'pv', 'DSP 35309', 6],
    ];

    const VARS_GRID = [
        ['grid_l1_volt', 'Netz L1 Spannung', 'F', 'GoodweET.Volt',   10, false, 'grid', 'DSP 35121'],
        ['grid_l1_curr', 'Netz L1 Strom',    'F', 'GoodweET.Ampere', 10, false, 'grid', 'DSP 35122'],
        ['grid_l1_freq', 'Netz L1 Frequenz', 'F', 'GoodweET.Hertz', 100, false, 'grid', 'DSP 35123'],
        ['grid_l1_pwr',  'Netz L1 Leistung', 'F', 'GoodweET.Watt',   1,  true,  'grid', 'DSP 35124'],
        ['grid_l2_volt', 'Netz L2 Spannung', 'F', 'GoodweET.Volt',   10, false, 'grid', 'DSP 35126'],
        ['grid_l2_curr', 'Netz L2 Strom',    'F', 'GoodweET.Ampere', 10, false, 'grid', 'DSP 35127'],
        ['grid_l2_freq', 'Netz L2 Frequenz', 'F', 'GoodweET.Hertz', 100, false, 'grid', 'DSP 35128'],
        ['grid_l2_pwr',  'Netz L2 Leistung', 'F', 'GoodweET.Watt',   1,  true,  'grid', 'DSP 35129'],
        ['grid_l3_volt', 'Netz L3 Spannung', 'F', 'GoodweET.Volt',   10, false, 'grid', 'DSP 35131'],
        ['grid_l3_curr', 'Netz L3 Strom',    'F', 'GoodweET.Ampere', 10, false, 'grid', 'DSP 35132'],
        ['grid_l3_freq', 'Netz L3 Frequenz', 'F', 'GoodweET.Hertz', 100, false, 'grid', 'DSP 35133'],
        ['grid_l3_pwr',  'Netz L3 Leistung', 'F', 'GoodweET.Watt',   1,  true,  'grid', 'DSP 35134'],
        ['inv_total',    'Inverter Gesamt',   'F', 'GoodweET.Watt',   1,  true,  'grid', 'DSP 35137'],
        ['grid_freq',    'Netzfrequenz',      'F', 'GoodweET.Hertz', 100, false, 'grid', 'SM 36014'],
    ];

    const VARS_BAT1 = [
        ['bat1_volt',     'Bat.1 Spannung',   'F', 'GoodweET.Volt',     10, false, 'bat1', 'DSP 35180'],
        ['bat1_curr',     'Bat.1 Strom',      'F', 'GoodweET.Ampere',   10, true,  'bat1', 'DSP 35181'],
        ['bat1_pwr',      'Bat.1 Leistung',   'F', 'GoodweET.Watt',      1, true,  'bat1', 'DSP 35182'],
        ['bat1_mode',     'Bat.1 Modus',      'I', 'GoodweET.BatMode',   1, true,  'bat1', 'DSP 35184'],
        ['bat1_soc',      'Bat.1 SOC',        'F', '~Battery.100',       1, true,  'bat1', 'BMS 47908'],
        ['bat1_soh',      'Bat.1 SOH',        'F', '~Intensity.100',     1, true,  'bat1', 'BMS 47909'],
        ['bat1_temp',     'Bat.1 Temperatur', 'F', '~Temperature',      10, true,  'bat1', 'BMS 47910'],
        ['bat1_cell_vmax','Bat.1 Zellspg max','I', 'GoodweET.MilliVolt', 1, false, 'bat1', 'BMS 37022'],
        ['bat1_cell_vmin','Bat.1 Zellspg min','I', 'GoodweET.MilliVolt', 1, false, 'bat1', 'BMS 37023'],
        ['bat1_chg_max_a','Bat.1 max. Ladestrom',   'F', 'GoodweET.Ampere', 10, false, 'bat1', 'BMS 47903'],
        ['bat1_dis_max_a','Bat.1 max. Entladestrom', 'F', 'GoodweET.Ampere', 10, false, 'bat1', 'BMS 47905'],
        ['bat1_bms_warn', 'Bat.1 BMS Warnung',       'I', '',                 1, true,  'bat1', 'BMS 47911'],
        ['bat1_bms_alarm','Bat.1 BMS Alarm',         'I', '',                 1, true,  'bat1', 'BMS 47913'],
    ];

    const VARS_BAT2 = [
        ['bat2_volt',     'Bat.2 Spannung',   'F', 'GoodweET.Volt',     10, false, 'bat2', 'DSP 35262'],
        ['bat2_curr',     'Bat.2 Strom',      'F', 'GoodweET.Ampere',   10, true,  'bat2', 'DSP 35263'],
        ['bat2_pwr',      'Bat.2 Leistung',   'F', 'GoodweET.Watt',      1, true,  'bat2', 'DSP 35264'],
        ['bat2_mode',     'Bat.2 Modus',      'I', 'GoodweET.BatMode',   1, true,  'bat2', 'DSP 35266'],
        ['bat2_soc',      'Bat.2 SOC',        'F', '~Battery.100',       1, true,  'bat2', 'BMS 47926'],
        ['bat2_soh',      'Bat.2 SOH',        'F', '~Intensity.100',     1, true,  'bat2', 'BMS 47927'],
        ['bat2_temp',     'Bat.2 Temperatur', 'F', '~Temperature',      10, true,  'bat2', 'BMS 47928'],
        ['bat2_cell_vmax','Bat.2 Zellspg max','I', 'GoodweET.MilliVolt', 1, false, 'bat2', 'BMS 39020'],
        ['bat2_cell_vmin','Bat.2 Zellspg min','I', 'GoodweET.MilliVolt', 1, false, 'bat2', 'BMS 39021'],
    ];

    const VARS_ENERGY = [
        ['e_pv_day',       'PV Heute',           'F', '~Electricity', 10, true,  'energy', 'DSP 35193'],
        ['e_pv_total',     'PV Gesamt',           'F', '~Electricity', 10, true,  'energy', 'DSP 35191'],
        ['e_sell_day',     'Einspeisung Heute',   'F', '~Electricity', 10, true,  'energy', 'DSP 35199'],
        ['e_buy_day',      'Bezug Heute',         'F', '~Electricity', 10, true,  'energy', 'DSP 35202'],
        ['e_load_day',     'Last Heute',          'F', '~Electricity', 10, true,  'energy', 'DSP 35205'],
        ['e_load_total',   'Last Gesamt',         'F', '~Electricity', 10, true,  'energy', 'DSP 35203'],
        ['e_charge_day',   'Bat. Laden Heute',    'F', '~Electricity', 10, true,  'energy', 'DSP 35208'],
        ['e_charge_total', 'Bat. Laden Gesamt',   'F', '~Electricity', 10, true,  'energy', 'DSP 35206'],
        ['e_disch_day',    'Bat. Entl. Heute',    'F', '~Electricity', 10, true,  'energy', 'DSP 35211'],
        ['e_disch_total',  'Bat. Entl. Gesamt',   'F', '~Electricity', 10, true,  'energy', 'DSP 35209'],
        ['work_hours',     'Betriebsstunden',     'F', '',           3600, false, 'energy', 'DSP 35197'],
    ];

    const VARS_METER = [
        ['mt_l1_volt', 'Netz L1 Spannung', 'F', 'GoodweET.Volt',   10, false, 'meter', 'SM 36052'],
        ['mt_l2_volt', 'Netz L2 Spannung', 'F', 'GoodweET.Volt',   10, false, 'meter', 'SM 36053'],
        ['mt_l3_volt', 'Netz L3 Spannung', 'F', 'GoodweET.Volt',   10, false, 'meter', 'SM 36054'],
        ['mt_l1_curr', 'Netz L1 Strom',    'F', 'GoodweET.Ampere', 10, false, 'meter', 'SM 36055'],
        ['mt_l2_curr', 'Netz L2 Strom',    'F', 'GoodweET.Ampere', 10, false, 'meter', 'SM 36056'],
        ['mt_l3_curr', 'Netz L3 Strom',    'F', 'GoodweET.Ampere', 10, false, 'meter', 'SM 36057'],
        ['mt_l1_pwr',  'Netz L1 Leistung', 'F', 'GoodweET.Watt',   1,  true,  'meter', 'SM 36019'],
        ['mt_l2_pwr',  'Netz L2 Leistung', 'F', 'GoodweET.Watt',   1,  true,  'meter', 'SM 36021'],
        ['mt_l3_pwr',  'Netz L3 Leistung', 'F', 'GoodweET.Watt',   1,  true,  'meter', 'SM 36023'],
    ];

    const VARS_TEMP = [
        ['temp_air',      'Lufttemperatur',  'F', '~Temperature', 10, false, 'temp', 'DSP 35174'],
        ['temp_module',   'Modultemperatur', 'F', '~Temperature', 10, true,  'temp', 'DSP 35175'],
        ['temp_heatsink', 'Kuehlkoerper',    'F', '~Temperature', 10, true,  'temp', 'DSP 35176'],
    ];

    const VARS_BACKUP = [
        ['backup_total', 'Backup Leistung',  'F', 'GoodweET.Watt', 1, true,  'backup', 'DSP 35169'],
        ['backup_active','Backup aktiv',     'B', '~Switch',       1, false, 'backup', 'RW 45252'],
    ];

    const VARS_ERRORS = [
        ['warn_code',  'Warncode',      'I', '', 1, true,  'errors', 'DSP 32000'],
        ['err_msg',    'Fehlercode',    'I', '', 1, true,  'errors', 'DSP 32002'],
        ['err_detail', 'Fehler Detail', 'S', '', 1, true,  'errors', ''],
    ];

    const VARS_DEVICE = [
        ['dev_sn',      'Seriennummer', 'S', '', 1, false, 'device', 'DSP 35003'],
        ['dev_model',   'Modell',       'S', '', 1, false, 'device', 'DSP 35011'],
        ['dev_rated_w', 'Nennleistung', 'I', '', 1, false, 'device', 'DSP 35001'],
        ['dev_fw_arm',  'Firmware ARM', 'I', '', 1, false, 'device', 'DSP 35019'],
        ['dev_fw_dsp',  'Firmware DSP', 'I', '', 1, false, 'device', 'DSP 35016'],
    ];

    const VARS_CONTROL = [
        ['ctl_work_mode',     'Steuermodus',          'I', 'GoodweET.WorkMode', 1, false, 'control', 'RW 47000'],
        ['ctl_ems_enable',    'EMS-Steuerung aktiv',  'B', '~Switch',           1, false, 'control', 'RW 47505'],
        ['ctl_ems_mode',      'EMS Leistungsmodus',   'I', 'GoodweET.EMSMode',  1, false, 'control', 'RW 47511'],
        ['ctl_ems_power',     'EMS Leistung (W)',     'I', 'GoodweET.WattEMS',  1, false, 'control', 'RW 47512'],
        ['ctl_export_enable', 'Einspeisung Ja/Nein',  'B', '~Switch',           1, false, 'control', 'RW 47509'],
        ['ctl_export_limit',  'Einspeisung Max. (W)', 'I', 'GoodweET.WattEMS',  1, false, 'control', 'RW 47510'],
        ['ctl_soc_min',       'SOC Min. Entladung',   'I', 'GoodweET.Percent',  1, false, 'control', 'RW 45356'],
        ['ctl_internet',      'Cloud-Verbindung',     'B', '~Switch',           1, false, 'control', 'RW 47017'],
        ['ctl_restart',       'WR Neustart',          'B', '~Switch',           1, false, 'control', 'WO 45220'],
    ];
}

// ---------------------------------------------------------------------------
// GoodweET — Hauptmodul (Auslese + Steuerung, Nature für das EMS)
// ---------------------------------------------------------------------------

class GoodweET extends IPSModule
{
    private const MODULE_GUID = '{1C4B7E2A-8F3D-5A9C-4E1B-7D2F9A3C6E8B}';

    public function Create()
    {
        parent::Create();

        $this->RegisterPropertyString('Host', '');
        $this->RegisterPropertyInteger('Port', 502);
        $this->RegisterPropertyInteger('UnitId', 247);

        $this->RegisterPropertyInteger('IntervalFast', 5);
        $this->RegisterPropertyInteger('IntervalSlow', 300);

        $this->RegisterPropertyBoolean('EnableMPPT1',  true);
        $this->RegisterPropertyBoolean('EnableMPPT2',  false);
        $this->RegisterPropertyBoolean('EnableMPPT3',  true);
        $this->RegisterPropertyBoolean('EnableMPPT4',  false);
        $this->RegisterPropertyBoolean('EnableMPPT5',  true);
        $this->RegisterPropertyBoolean('EnableMPPT6',  false);
        $this->RegisterPropertyBoolean('GroupGrid',    true);
        $this->RegisterPropertyBoolean('GroupBat1',    true);
        $this->RegisterPropertyBoolean('GroupBat2',    true);
        $this->RegisterPropertyBoolean('GroupEnergy',  true);
        $this->RegisterPropertyBoolean('GroupMeter',   true);
        $this->RegisterPropertyBoolean('GroupTemp',    true);
        $this->RegisterPropertyBoolean('GroupBackup',  true);
        $this->RegisterPropertyBoolean('GroupErrors',  true);
        $this->RegisterPropertyBoolean('GroupDevice',  true);
        $this->RegisterPropertyBoolean('GroupControl', true);

        $this->RegisterTimer('FastTimer', 0, 'GWET_ReadFast($_IPS[\'TARGET\']);');
        $this->RegisterTimer('SlowTimer', 0, 'GWET_ReadSlow($_IPS[\'TARGET\']);');

        $this->RegisterAttributeBoolean('DeviceInfoRead', false);
        $this->RegisterAttributeInteger('Controller', 0);   // Instanz-ID des steuernden EMS
    }

    public function Destroy()
    {
        parent::Destroy();
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();

        $this->CreateProfiles();
        $this->RegisterVariables();

        $host = $this->ReadPropertyString('Host');
        if ($host === '') {
            $this->SetStatus(104);
            $this->SetTimerInterval('FastTimer', 0);
            $this->SetTimerInterval('SlowTimer', 0);
            return;
        }

        $this->SetTimerInterval('FastTimer', $this->ReadPropertyInteger('IntervalFast') * 1000);
        $this->SetTimerInterval('SlowTimer', $this->ReadPropertyInteger('IntervalSlow') * 1000);
        $this->WriteAttributeBoolean('DeviceInfoRead', false);
        $this->SetStatus(102);
    }

    // -----------------------------------------------------------------------
    // Öffentliche Timer-Methoden
    // -----------------------------------------------------------------------

    public function ReadFast()
    {
        if (!$this->ReadAttributeBoolean('DeviceInfoRead') && $this->ReadPropertyBoolean('GroupDevice')) {
            $this->ReadDeviceInfo();
        }
        $this->ReadInverterData();
    }

    public function ReadSlow()
    {
        $this->ReadEnergyData();
        $this->ReadErrorData();
    }

    // -----------------------------------------------------------------------
    // Nature-Schnittstelle (vom EMS aufgerufen)
    // -----------------------------------------------------------------------

    /**
     * Setzt den Wechselrichter auf einen vendor-neutralen Intent.
     * $intent: GoodweRegisterMap::INTENT_* (AUTO/PV_SELFUSE/GRID_CHARGE/
     *          DISCHARGE/EXPORT/STANDBY)
     * $watt:   gewünschte Leistung (nur bei GRID_CHARGE relevant), 0..34500
     * Schreibt realtime auf die EMS-Bank 47505/47511/47512 — KEIN Scheduler.
     */
    public function ApplySetpoint(int $intent, int $watt = 0)
    {
        $map  = GoodweRegisterMap::INTENT_TO_MODE;
        $mode = isset($map[$intent]) ? $map[$intent] : 1;

        $host   = $this->ReadPropertyString('Host');
        $port   = $this->ReadPropertyInteger('Port');
        $unitId = $this->ReadPropertyInteger('UnitId');
        if ($host === '') { return false; }

        // EMS-Steuerung scharfschalten (47505 = 2)
        $this->modbusWriteSingle($host, $port, $unitId, GoodweRegisterMap::REG_EMS_ENABLE, 2);

        // Leistungsmodus setzen (47511)
        $ok = $this->modbusWriteSingle($host, $port, $unitId, GoodweRegisterMap::REG_EMS_POWER_MODE, $mode);
        $this->SetVarInt('ctl_ems_mode', $mode);

        // Leistung nur bei Netz-Laden schreiben (47512, U16, gedeckelt)
        if ($intent === GoodweRegisterMap::INTENT_GRID_CHARGE) {
            $w = max(0, min(GoodweRegisterMap::EMS_POWER_MAX, $watt));
            // zusätzlich auf die vom BMS erlaubte Ladeleistung klemmen
            $maxVid = @$this->GetIDForIdent('bat_charge_max_w');
            if ($maxVid) {
                $allowed = (int)GetValue($maxVid);
                if ($allowed > 0 && $allowed < $w) {
                    $w = $allowed;
                    $this->SendDebug('ApplySetpoint', "Auf BMS-Grenze geklemmt: $allowed W", 0);
                }
            }
            $this->modbusWriteSingle($host, $port, $unitId, GoodweRegisterMap::REG_EMS_POWER_SET, $w);
            $this->SetVarInt('ctl_ems_power', $w);
        }

        $this->SendDebug('ApplySetpoint', "Intent=$intent -> Mode=$mode Watt=$watt", 0);
        return $ok;
    }

    /** Gibt die wichtigsten normierten Messwerte als JSON zurück. */
    public function GetChannels()
    {
        $g = function($ident) {
            $vid = @$this->GetIDForIdent($ident);
            return $vid ? GetValue($vid) : null;
        };
        return json_encode([
            'soc'        => $g('soc'),
            'pv_total'   => $g('pv_total'),
            'grid_total' => $g('meter_total'),
            'bat_power'  => $g('bat_total_pwr'),
            'wr_total'   => $g('ac_power'),
            'island'     => $g('island'),
            'bat1_soc'   => $g('bat1_soc'),
            'bat1_soh'   => $g('bat1_soh'),
            'bat2_soc'   => $g('bat2_soc'),
            'bat2_soh'   => $g('bat2_soh'),
        ]);
    }

    /** Merkt sich die steuernde EMS-Instanz (für Anzeige/Sperren). */
    public function AttachController(int $emsInstanceId)
    {
        $this->WriteAttributeInteger('Controller', $emsInstanceId);
        return true;
    }

    // -----------------------------------------------------------------------
    // Lese-Methoden
    // -----------------------------------------------------------------------

    private function ReadInverterData()
    {
        $host   = $this->ReadPropertyString('Host');
        $port   = $this->ReadPropertyInteger('Port');
        $unitId = $this->ReadPropertyInteger('UnitId');

        $inv     = $this->modbusRead($host, $port, $unitId, 35103, 42);
        $bat1blk = $this->modbusRead($host, $port, $unitId, 35174, 18);
        $bat2blk = $this->modbusRead($host, $port, $unitId, 35262, 7);
        $pvext   = $this->modbusRead($host, $port, $unitId, 35301, 41);
        $meter   = $this->modbusRead($host, $port, $unitId, 36019, 39);
        $bms1    = $this->modbusRead($host, $port, $unitId, 47902, 14);  // inkl. Lade-/Entladegrenzen
        $bms2    = $this->modbusRead($host, $port, $unitId, 47924, 5);

        $ok = ($inv !== null && $bat1blk !== null);
        $this->SetVarBool('connected', $ok);

        if (!$ok) {
            $this->SendDebug('ReadFast', 'Modbus-Fehler: keine Antwort', 0);
            return;
        }

        // Basis
        $pvTotal = ($pvext !== null) ? (float)$this->u32($pvext, 0) : 0.0;   // 35301
        $this->SetVarFloat('pv_total', $pvTotal);

        // Betriebsmodus (Register 47000)
        $wm = $this->modbusRead($host, $port, $unitId, 47000, 1);
        if ($wm !== null) {
            $this->SetVarInt('work_mode', $this->u16($wm, 0));
        }

        // SOC aus ARM 10472 (WBMS 47908/47926 liefern auf dieser Firmware
        // 0xFFFF — das sind Fremd-BMS-Injektionsregister, kein Auslesewert).
        // Pro-String-SOC ist nicht separat verfügbar → bat1/bat2 = kombiniert.
        $armSoc = $this->modbusRead($host, $port, $unitId, 10472, 1);
        $socVal = ($armSoc !== null) ? (float)$this->u16($armSoc, 0) : 0.0;
        if ($socVal > 100.0) { $socVal = 0.0; }   // 0xFFFF-Schutz
        $this->SetVarFloat('soc', $socVal);
        $bat2Active = $this->ReadPropertyBoolean('GroupBat2') && ($bat2blk !== null);

        // PV-Details per MPPT (unverändert aus inv/pvext-Block)
        if ($this->ReadPropertyBoolean('EnableMPPT1')) {
            $this->SetVarFloat('pv1_voltage', $this->u16($inv, 0) / 10.0);
            $this->SetVarFloat('pv1_current', $this->u16($inv, 1) / 10.0);
            $this->SetVarFloat('pv1_power',   (float)$this->u32($inv, 2));
        }
        if ($this->ReadPropertyBoolean('EnableMPPT2')) {
            $this->SetVarFloat('pv2_voltage', $this->u16($inv, 4) / 10.0);
            $this->SetVarFloat('pv2_current', $this->u16($inv, 5) / 10.0);
            $this->SetVarFloat('pv2_power',   (float)$this->u32($inv, 6));
        }
        if ($this->ReadPropertyBoolean('EnableMPPT3')) {
            $this->SetVarFloat('pv3_voltage', $this->u16($inv, 8) / 10.0);
            $this->SetVarFloat('pv3_current', $this->u16($inv, 9) / 10.0);
            $this->SetVarFloat('pv3_power',   (float)$this->u32($inv, 10));
        }
        if ($this->ReadPropertyBoolean('EnableMPPT4')) {
            $this->SetVarFloat('pv4_voltage', $this->u16($inv, 12) / 10.0);
            $this->SetVarFloat('pv4_current', $this->u16($inv, 13) / 10.0);
            $this->SetVarFloat('pv4_power',   (float)$this->u32($inv, 14));
        }
        if ($pvext !== null) {
            if ($this->ReadPropertyBoolean('EnableMPPT5')) {
                $this->SetVarFloat('pv5_voltage', $this->u16($pvext, 3)  / 10.0);
                $this->SetVarFloat('pv5_current', $this->u16($pvext, 4)  / 10.0);
                $p5 = $this->u16($pvext, 40);
                if ($p5 !== 0xFFFF) { $this->SetVarFloat('pv5_power', (float)$p5); }
            }
            if ($this->ReadPropertyBoolean('EnableMPPT6')) {
                $this->SetVarFloat('pv6_voltage', $this->u16($pvext, 6)  / 10.0);
                $this->SetVarFloat('pv6_current', $this->u16($pvext, 7)  / 10.0);
                $p6 = $this->u32($pvext, 8);
                if ($p6 !== 0xFFFFFFFF) { $this->SetVarFloat('pv6_power', (float)$p6); }
            }
        }

        // Netz R/S/T (Inverter-AC) + Netzmodus + Inselerkennung
        $gridMode = $this->u16($inv, 33);   // 35136
        if ($this->ReadPropertyBoolean('GroupGrid')) {
            $this->SetVarFloat('grid_l1_volt', $this->u16($inv, 18) / 10.0);
            $this->SetVarFloat('grid_l1_curr', $this->u16($inv, 19) / 10.0);
            $this->SetVarFloat('grid_l1_freq', $this->u16($inv, 20) / 100.0);
            $this->SetVarFloat('grid_l1_pwr',  (float)$this->s32($inv, 21));
            $this->SetVarFloat('grid_l2_volt', $this->u16($inv, 23) / 10.0);
            $this->SetVarFloat('grid_l2_curr', $this->u16($inv, 24) / 10.0);
            $this->SetVarFloat('grid_l2_freq', $this->u16($inv, 25) / 100.0);
            $this->SetVarFloat('grid_l2_pwr',  (float)$this->s32($inv, 26));
            $this->SetVarFloat('grid_l3_volt', $this->u16($inv, 28) / 10.0);
            $this->SetVarFloat('grid_l3_curr', $this->u16($inv, 29) / 10.0);
            $this->SetVarFloat('grid_l3_freq', $this->u16($inv, 30) / 100.0);
            $this->SetVarFloat('grid_l3_pwr',  (float)$this->s32($inv, 31));
            $this->SetVarFloat('inv_total',   (float)$this->s32($inv, 34));
        }
        $this->SetVarInt('grid_mode',   $gridMode);
        $this->SetVarFloat('ac_power',  (float)$this->s32($inv, 36));
        // Inselbetrieb: grid_mode 17 (Bypass) / 18 (Inselbetrieb)
        $this->SetVarBool('island', ($gridMode === 17 || $gridMode === 18));

        // Netz-Gesamtleistung aus SmartMeter (36025)
        if ($meter !== null) {
            $this->SetVarFloat('meter_total', (float)$this->s32($meter, 6));
        }

        // Batterie 1 (Leistung/Modus aus DSP, SOC/SOH/Temp aus BMS)
        if ($this->ReadPropertyBoolean('GroupBat1')) {
            $this->SetVarFloat('bat1_volt', $this->u16($bat1blk, 6)  / 10.0);
            $this->SetVarFloat('bat1_curr', $this->s16($bat1blk, 7)  / 10.0);
            $this->SetVarFloat('bat1_pwr',  (float)$this->s32($bat1blk, 8));
            $this->SetVarInt('bat1_mode',   $this->u16($bat1blk, 10));
            $this->SetVarFloat('bat1_soc',  $socVal);
            if ($bms1 !== null) {
                $soh1 = $this->u16($bms1, 7);                                   // 47909
                if ($soh1 !== 0xFFFF) { $this->SetVarFloat('bat1_soh', (float)$soh1); }
                $this->SetVarFloat('bat1_chg_max_a', $this->u16($bms1, 1) / 10.0); // 47903
                $this->SetVarFloat('bat1_dis_max_a', $this->u16($bms1, 3) / 10.0); // 47905
                $this->SetVarInt('bat1_bms_warn',  $this->u32($bms1, 9));       // 47911
                $this->SetVarInt('bat1_bms_alarm', $this->u32($bms1, 11));      // 47913
            }
            // Pack-Temp + Zellspannungen aus 37000er-Block (WBMS-Temp 47910 = 0xFFFF)
            $bdet1 = $this->modbusRead($host, $port, $unitId, 37003, 21);
            if ($bdet1 !== null) {
                $this->SetVarFloat('bat1_temp',    $this->s16($bdet1, 0) / 10.0);  // 37003 Pack
                $this->SetVarInt('bat1_cell_vmax', $this->u16($bdet1, 19));        // 37022
                $this->SetVarInt('bat1_cell_vmin', $this->u16($bdet1, 20));        // 37023
            }
        }

        // Temperaturen (Geräte-Temps aus DSP-Block)
        if ($this->ReadPropertyBoolean('GroupTemp')) {
            $this->SetVarFloat('temp_air',      $this->s16($bat1blk, 0) / 10.0);
            $this->SetVarFloat('temp_module',   $this->s16($bat1blk, 1) / 10.0);
            $this->SetVarFloat('temp_heatsink', $this->s16($bat1blk, 2) / 10.0);
        }

        // Batterie 2
        if ($bat2Active) {
            $this->SetVarFloat('bat2_volt', $this->u16($bat2blk, 0)  / 10.0);
            $this->SetVarFloat('bat2_curr', $this->s16($bat2blk, 1)  / 10.0);
            $this->SetVarFloat('bat2_pwr',  (float)$this->s32($bat2blk, 2));
            $this->SetVarInt('bat2_mode',   $this->u16($bat2blk, 4));
            $this->SetVarFloat('bat2_soc',  $socVal);
            if ($bms2 !== null) {
                $soh2 = $this->u16($bms2, 3);                                   // 47927
                if ($soh2 !== 0xFFFF) { $this->SetVarFloat('bat2_soh', (float)$soh2); }
            }
            $bdet2 = $this->modbusRead($host, $port, $unitId, 39001, 21);
            if ($bdet2 !== null) {
                $this->SetVarFloat('bat2_temp',    $this->s16($bdet2, 0) / 10.0);  // 39001 Pack
                $this->SetVarInt('bat2_cell_vmax', $this->u16($bdet2, 19));        // 39020
                $this->SetVarInt('bat2_cell_vmin', $this->u16($bdet2, 20));        // 39021
            }
        }

        // Batteriegesamt (berechnet)
        $b1p = $this->s32($bat1blk, 8);
        $b2p = ($bat2blk !== null) ? $this->s32($bat2blk, 2) : 0;
        $this->SetVarFloat('bat_total_pwr', (float)($b1p + $b2p));

        // BMS-Lade-/Entladegrenzen → erlaubte Systemleistung (Deckel für ApplySetpoint)
        // P = max. Strom (A) × Batteriespannung (V). Quelle: OpenEMS WBMS 47902-47905.
        if ($bms1 !== null) {
            $v1    = $this->u16($bat1blk, 6) / 10.0;     // 35180 Batteriespannung (DSP, zuverlässig)
            $chgA1 = $this->u16($bms1, 1) / 10.0;        // 47903
            $disA1 = $this->u16($bms1, 3) / 10.0;        // 47905
            $chgMaxW = $chgA1 * $v1;
            $disMaxW = $disA1 * $v1;
            if ($bat2Active) {
                // Bat2-Grenzen per +18-Offset (47920/47922) — an Anlage verifizieren
                $lim2 = $this->modbusRead($host, $port, $unitId, 47920, 4);
                $v2   = ($bat2blk !== null) ? $this->u16($bat2blk, 0) / 10.0 : $v1;  // 35262
                if ($lim2 !== null) {
                    $chgMaxW += ($this->u16($lim2, 1) / 10.0) * $v2;
                    $disMaxW += ($this->u16($lim2, 3) / 10.0) * $v2;
                } else {
                    $chgMaxW *= 2.0;   // symmetrische Strings angenommen
                    $disMaxW *= 2.0;
                }
            }
            $this->SetVarFloat('bat_charge_max_w',    $chgMaxW);
            $this->SetVarFloat('bat_discharge_max_w', $disMaxW);
        }

        // Smart Meter (Phasen) + Frequenz
        if ($this->ReadPropertyBoolean('GroupMeter') && $meter !== null) {
            $this->SetVarFloat('mt_l1_pwr',  (float)$this->s32($meter, 0));
            $this->SetVarFloat('mt_l2_pwr',  (float)$this->s32($meter, 2));
            $this->SetVarFloat('mt_l3_pwr',  (float)$this->s32($meter, 4));
            $this->SetVarFloat('mt_l1_volt', $this->u16($meter, 33) / 10.0);
            $this->SetVarFloat('mt_l2_volt', $this->u16($meter, 34) / 10.0);
            $this->SetVarFloat('mt_l3_volt', $this->u16($meter, 35) / 10.0);
            $this->SetVarFloat('mt_l1_curr', $this->u16($meter, 36) / 10.0);
            $this->SetVarFloat('mt_l2_curr', $this->u16($meter, 37) / 10.0);
            $this->SetVarFloat('mt_l3_curr', $this->u16($meter, 38) / 10.0);
            $freqBlk = $this->modbusRead($host, $port, $unitId, 36014, 1);
            if ($freqBlk !== null) {
                $this->SetVarFloat('grid_freq', $this->u16($freqBlk, 0) / 100.0);
            }
        }

        // Backup / Inselleistung
        if ($this->ReadPropertyBoolean('GroupBackup')) {
            $bk = $this->modbusRead($host, $port, $unitId, 35169, 2);
            if ($bk !== null) {
                $this->SetVarFloat('backup_total', (float)$this->s32($bk, 0));
            }
            $bkSt = $this->modbusRead($host, $port, $unitId, 45252, 1);
            if ($bkSt !== null) {
                $this->SetVarBool('backup_active', $this->u16($bkSt, 0) > 0);
            }
        }
    }

    private function ReadEnergyData()
    {
        if (!$this->ReadPropertyBoolean('GroupEnergy')) {
            return;
        }
        $host   = $this->ReadPropertyString('Host');
        $port   = $this->ReadPropertyInteger('Port');
        $unitId = $this->ReadPropertyInteger('UnitId');

        $e = $this->modbusRead($host, $port, $unitId, 35191, 22);
        if ($e === null) {
            return;
        }
        $this->SetVarFloat('e_pv_total',     $this->u32($e, 0)  / 10.0);   // 35191
        $this->SetVarFloat('e_pv_day',       $this->u32($e, 2)  / 10.0);   // 35193
        $this->SetVarFloat('work_hours',     (float)$this->u32($e, 6));    // 35197
        $this->SetVarFloat('e_sell_day',     $this->u16($e, 8)  / 10.0);   // 35199
        $this->SetVarFloat('e_buy_day',      $this->u16($e, 11) / 10.0);   // 35202
        $this->SetVarFloat('e_load_total',   $this->u32($e, 12) / 10.0);   // 35203
        $this->SetVarFloat('e_load_day',     $this->u16($e, 14) / 10.0);   // 35205
        $this->SetVarFloat('e_charge_total', $this->u32($e, 15) / 10.0);   // 35206
        $this->SetVarFloat('e_charge_day',   $this->u16($e, 17) / 10.0);   // 35208
        $this->SetVarFloat('e_disch_total',  $this->u32($e, 18) / 10.0);   // 35209
        $this->SetVarFloat('e_disch_day',    $this->u16($e, 20) / 10.0);   // 35211
    }

    private function ReadErrorData()
    {
        if (!$this->ReadPropertyBoolean('GroupErrors')) {
            return;
        }
        $host   = $this->ReadPropertyString('Host');
        $port   = $this->ReadPropertyInteger('Port');
        $unitId = $this->ReadPropertyInteger('UnitId');

        $err = $this->modbusRead($host, $port, $unitId, 32000, 17);
        if ($err === null) {
            return;
        }
        $this->SetVarInt('warn_code', $this->u16($err, 0));
        $this->SetVarInt('err_msg',   $this->u16($err, 2));

        $utility = $this->u16($err, 0);
        $detail  = [];
        if ($utility & 0x01) { $detail[] = 'Netz-Ueberspannung'; }
        if ($utility & 0x02) { $detail[] = 'Netz-Unterspannung'; }
        if ($utility & 0x04) { $detail[] = 'Netz-Ueberfrequenz'; }
        if ($utility & 0x08) { $detail[] = 'Netz-Unterfrequenz'; }
        if ($utility & 0x10) { $detail[] = 'Netz-Ueberstrom'; }
        $sys = $this->u16($err, 2);
        if ($sys & 0x01)     { $detail[] = 'Systemfehler 1'; }
        $this->SetVarStr('err_detail', empty($detail) ? 'OK' : implode(', ', $detail));
    }

    private function ReadDeviceInfo()
    {
        $host   = $this->ReadPropertyString('Host');
        $port   = $this->ReadPropertyInteger('Port');
        $unitId = $this->ReadPropertyInteger('UnitId');

        $dev = $this->modbusRead($host, $port, $unitId, 35001, 27);
        if ($dev === null) {
            return;
        }
        $this->SetVarInt('dev_rated_w', $this->u16($dev, 0));
        $this->SetVarStr('dev_sn',      $this->readStr($dev, 2, 8));
        $this->SetVarStr('dev_model',   $this->readStr($dev, 10, 5));
        $this->SetVarInt('dev_fw_dsp',  $this->u16($dev, 15));
        $this->SetVarInt('dev_fw_arm',  $this->u16($dev, 18));
        $this->WriteAttributeBoolean('DeviceInfoRead', true);
    }

    // -----------------------------------------------------------------------
    // Schreib-Aktionen
    // -----------------------------------------------------------------------

    public function RequestAction($Ident, $Value)
    {
        $host   = $this->ReadPropertyString('Host');
        $port   = $this->ReadPropertyInteger('Port');
        $unitId = $this->ReadPropertyInteger('UnitId');

        switch ($Ident) {
            case 'ctl_work_mode':
                $val = (int)$Value;
                if ($val < 0 || $val > 5) { return; }
                if ($this->modbusWriteSingle($host, $port, $unitId, GoodweRegisterMap::REG_WORK_MODE, $val)) {
                    $this->SetVarInt('ctl_work_mode', $val);
                    $this->SetVarInt('work_mode', $val);
                }
                break;

            case 'ctl_ems_enable':
                $val = (bool)$Value ? 2 : 0;
                if ($this->modbusWriteSingle($host, $port, $unitId, GoodweRegisterMap::REG_EMS_ENABLE, $val)) {
                    $this->SetVarBool('ctl_ems_enable', (bool)$Value);
                }
                break;

            case 'ctl_ems_mode':
                $val = (int)$Value;
                if ($val < 0 || $val > 12) { return; }
                if ($this->modbusWriteSingle($host, $port, $unitId, GoodweRegisterMap::REG_EMS_POWER_MODE, $val)) {
                    $this->SetVarInt('ctl_ems_mode', $val);
                }
                break;

            case 'ctl_ems_power':
                $val = max(0, min(GoodweRegisterMap::EMS_POWER_MAX, (int)$Value));
                if ($this->modbusWriteSingle($host, $port, $unitId, GoodweRegisterMap::REG_EMS_POWER_SET, $val)) {
                    $this->SetVarInt('ctl_ems_power', $val);
                }
                break;

            case 'ctl_export_enable':
                $val = (bool)$Value ? 1 : 0;
                if ($this->modbusWriteSingle($host, $port, $unitId, GoodweRegisterMap::REG_FEED_POWER_ENABLE, $val)) {
                    $this->SetVarBool('ctl_export_enable', (bool)$Value);
                }
                break;

            case 'ctl_export_limit':
                $val = max(0, min(GoodweRegisterMap::EMS_POWER_MAX, (int)$Value));
                if ($this->modbusWriteSingle($host, $port, $unitId, GoodweRegisterMap::REG_FEED_POWER_LIMIT, $val)) {
                    $this->SetVarInt('ctl_export_limit', $val);
                }
                break;

            case 'ctl_soc_min':
                $val = max(0, min(100, (int)$Value));
                if ($this->modbusWriteSingle($host, $port, $unitId, GoodweRegisterMap::REG_SOC_MIN, $val)) {
                    $this->SetVarInt('ctl_soc_min', $val);
                }
                break;

            case 'ctl_internet':
                // true = Internet AN → Register 47017 = 0
                $val = (bool)$Value ? 0 : 1;
                if ($this->modbusWriteSingle($host, $port, $unitId, GoodweRegisterMap::REG_INTERNET_MODE, $val)) {
                    $this->SetVarBool('ctl_internet', (bool)$Value);
                }
                break;

            case 'ctl_restart':
                if ((bool)$Value) {
                    $this->modbusWriteSingle($host, $port, $unitId, GoodweRegisterMap::REG_RESTART, 1);
                    IPS_Sleep(500);
                    $this->SetVarBool('ctl_restart', false);
                }
                break;
        }
    }

    // -----------------------------------------------------------------------
    // Variablen-Registrierung
    // -----------------------------------------------------------------------

    private function RegisterVariables()
    {
        // Obsolete Idents aus früheren Versionen entfernen
        $obsolete = [
            'grid_r_volt','grid_r_curr','grid_r_freq','grid_r_pwr',
            'grid_s_volt','grid_s_curr','grid_s_freq','grid_s_pwr',
            'grid_t_volt','grid_t_curr','grid_t_freq','grid_t_pwr',
            'mt_r_volt','mt_r_curr','mt_r_pwr',
            'mt_s_volt','mt_s_curr','mt_s_pwr',
            'mt_t_volt','mt_t_curr','mt_t_pwr',
            'mt_e_sell','mt_e_buy','e_buy_total','e_sell_total','temp_bms',
            'ctl_feed_enable','ctl_feed_limit','ctl_soc_max','ctl_peak_pwr',
        ];
        foreach ($obsolete as $ident) {
            $this->UnregVarIfExists($ident);
        }

        $pos = 0;
        foreach (GoodweRegisterMap::VARS_BASE as $v) {
            $this->RegisterVar($v, $pos++, false);
        }

        foreach (GoodweRegisterMap::VARS_PV as $v) {
            $mppt = $v[8] ?? 0;
            if ($mppt > 0 && $this->ReadPropertyBoolean('EnableMPPT' . $mppt)) {
                $this->RegisterVar($v, $pos++, false);
            } else {
                $this->UnregVarIfExists($v[0]);
            }
        }

        $groups = [
            'GroupGrid'    => GoodweRegisterMap::VARS_GRID,
            'GroupBat1'    => GoodweRegisterMap::VARS_BAT1,
            'GroupBat2'    => GoodweRegisterMap::VARS_BAT2,
            'GroupEnergy'  => GoodweRegisterMap::VARS_ENERGY,
            'GroupMeter'   => GoodweRegisterMap::VARS_METER,
            'GroupTemp'    => GoodweRegisterMap::VARS_TEMP,
            'GroupBackup'  => GoodweRegisterMap::VARS_BACKUP,
            'GroupErrors'  => GoodweRegisterMap::VARS_ERRORS,
            'GroupDevice'  => GoodweRegisterMap::VARS_DEVICE,
            'GroupControl' => GoodweRegisterMap::VARS_CONTROL,
        ];

        foreach ($groups as $prop => $varList) {
            $enabled = $this->ReadPropertyBoolean($prop);
            foreach ($varList as $v) {
                if ($enabled) {
                    $isCtrl = ($v[6] === 'control');
                    $this->RegisterVar($v, $pos++, $isCtrl);
                } else {
                    $this->UnregVarIfExists($v[0]);
                }
            }
        }
    }

    private function RegisterVar(array $def, int $pos, bool $withAction)
    {
        [$ident, $caption, $type, $profile, , $archive] = $def;
        $reg = isset($def[7]) ? $def[7] : '';
        $vid = @$this->GetIDForIdent($ident);

        switch ($type) {
            case 'F':
                if (!$vid) { $vid = $this->RegisterVariableFloat($ident, $caption, $profile, $pos); }
                break;
            case 'I':
                if (!$vid) { $vid = $this->RegisterVariableInteger($ident, $caption, $profile, $pos); }
                break;
            case 'B':
                if (!$vid) { $vid = $this->RegisterVariableBoolean($ident, $caption, $profile, $pos); }
                break;
            case 'S':
                if (!$vid) { $vid = $this->RegisterVariableString($ident, $caption, $profile, $pos); }
                break;
        }

        if ($vid) {
            IPS_SetName($vid, $caption);
            if ($reg !== '') {
                IPS_SetInfo($vid, $reg);
            }
        }

        if ($vid && $withAction) {
            $this->EnableAction($ident);
        }
        if ($vid && $archive) {
            $this->SetArchive($vid);
        }
    }

    private function UnregVarIfExists(string $ident)
    {
        if (@$this->GetIDForIdent($ident)) {
            $this->UnregisterVariable($ident);
        }
    }

    private function SetArchive(int $vid)
    {
        $archiveIDs = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}');
        if (count($archiveIDs) > 0) {
            AC_SetLoggingStatus($archiveIDs[0], $vid, true);
            AC_SetAggregationType($archiveIDs[0], $vid, 0);
        }
    }

    // -----------------------------------------------------------------------
    // Variable setzen
    // -----------------------------------------------------------------------

    private function SetVarFloat(string $ident, float $value)
    {
        $vid = @$this->GetIDForIdent($ident);
        if ($vid) { SetValueFloat($vid, $value); }
    }

    private function SetVarInt(string $ident, int $value)
    {
        $vid = @$this->GetIDForIdent($ident);
        if ($vid) { SetValueInteger($vid, $value); }
    }

    private function SetVarBool(string $ident, bool $value)
    {
        $vid = @$this->GetIDForIdent($ident);
        if ($vid) { SetValueBoolean($vid, $value); }
    }

    private function SetVarStr(string $ident, string $value)
    {
        $vid = @$this->GetIDForIdent($ident);
        if ($vid) { SetValueString($vid, $value); }
    }

    // -----------------------------------------------------------------------
    // Profile
    // -----------------------------------------------------------------------

    private function CreateProfiles()
    {
        $this->CreateProfile('GoodweET.Watt',     VARIABLETYPE_FLOAT,   ' W',  -40000.0, 40000.0, 1.0,  0);
        $this->CreateProfile('GoodweET.Volt',     VARIABLETYPE_FLOAT,   ' V',       0.0,  1000.0, 0.1,  1);
        $this->CreateProfile('GoodweET.Ampere',   VARIABLETYPE_FLOAT,   ' A',    -200.0,   200.0, 0.1,  1);
        $this->CreateProfile('GoodweET.Hertz',    VARIABLETYPE_FLOAT,   ' Hz',     45.0,    65.0, 0.01, 2);
        $this->CreateProfile('GoodweET.Percent',  VARIABLETYPE_INTEGER, ' %',          0,     100, 1,    0);
        $this->CreateProfile('GoodweET.MilliVolt',VARIABLETYPE_INTEGER, ' mV',         0,    5000, 1,    0);
        $this->CreateProfile('GoodweET.WattEMS',  VARIABLETYPE_INTEGER, ' W',          0,   34500, 1,    0);

        if (!IPS_VariableProfileExists('GoodweET.WorkMode')) {
            IPS_CreateVariableProfile('GoodweET.WorkMode', VARIABLETYPE_INTEGER);
        }
        $wmColors = [0xF5A623, 0x7A8A99, 0x2BB3C0, 0x27D07F, 0xE74C3C, 0xF39C12];
        foreach (GoodweRegisterMap::WORK_MODES as $k => $label) {
            IPS_SetVariableProfileAssociation('GoodweET.WorkMode', $k, $label, '', $wmColors[$k] ?? 0x7A8A99);
        }

        if (!IPS_VariableProfileExists('GoodweET.EMSMode')) {
            IPS_CreateVariableProfile('GoodweET.EMSMode', VARIABLETYPE_INTEGER);
        }
        foreach (GoodweRegisterMap::EMS_MODES as $k => $label) {
            IPS_SetVariableProfileAssociation('GoodweET.EMSMode', $k, $label, '', 0x7A8A99);
        }

        if (!IPS_VariableProfileExists('GoodweET.BatMode')) {
            IPS_CreateVariableProfile('GoodweET.BatMode', VARIABLETYPE_INTEGER);
        }
        foreach (GoodweRegisterMap::BAT_MODES as $k => $label) {
            IPS_SetVariableProfileAssociation('GoodweET.BatMode', $k, $label, '', 0x7A8A99);
        }

        if (!IPS_VariableProfileExists('GoodweET.GridMode')) {
            IPS_CreateVariableProfile('GoodweET.GridMode', VARIABLETYPE_INTEGER);
        }
        foreach (GoodweRegisterMap::GRID_MODES as $k => $label) {
            IPS_SetVariableProfileAssociation('GoodweET.GridMode', $k, $label, '', 0x7A8A99);
        }
    }

    private function CreateProfile(string $name, int $type, string $suffix, float $min, float $max, float $step, int $digits)
    {
        if (!IPS_VariableProfileExists($name)) {
            IPS_CreateVariableProfile($name, $type);
        }
        IPS_SetVariableProfileDigits($name, $digits);
        IPS_SetVariableProfileText($name, '', $suffix);
        IPS_SetVariableProfileValues($name, $min, $max, $step);
    }

    // -----------------------------------------------------------------------
    // Modbus TCP
    // -----------------------------------------------------------------------

    private function modbusRead(string $host, int $port, int $unitId, int $startReg, int $count)
    {
        $sock = @fsockopen($host, $port, $errno, $errstr, 3.0);
        if ($sock === false) {
            $this->SendDebug('Modbus', "Verbindung fehlgeschlagen: $errstr ($errno)", 0);
            return null;
        }
        stream_set_timeout($sock, 3);

        $tid  = mt_rand(1, 65535);
        $pdu  = pack('Cnn', 0x03, $startReg, $count);
        $mbap = pack('nnn', $tid, 0, strlen($pdu) + 1) . chr($unitId);

        fwrite($sock, $mbap . $pdu);

        $response = '';
        $deadline  = microtime(true) + 3.0;
        while (microtime(true) < $deadline) {
            $chunk = @fread($sock, 512);
            if ($chunk === false || $chunk === '') { break; }
            $response .= $chunk;
            if (strlen($response) >= 9) {
                $byteCount = ord($response[8]);
                if (strlen($response) >= 9 + $byteCount) { break; }
            }
        }
        fclose($sock);

        if (strlen($response) < 9) { return null; }

        $fc = ord($response[7]);
        if ($fc & 0x80) {
            $this->SendDebug('Modbus', 'Exception FC=' . $fc . ' Reg=' . $startReg, 0);
            return null;
        }
        if ($fc !== 0x03) { return null; }

        $byteCount = ord($response[8]);
        $data      = substr($response, 9, $byteCount);

        $regs = [];
        for ($i = 0; $i < $count && ($i * 2 + 1) < strlen($data); $i++) {
            $regs[$i] = (ord($data[$i * 2]) << 8) | ord($data[$i * 2 + 1]);
        }
        return $regs;
    }

    private function modbusWriteSingle(string $host, int $port, int $unitId, int $reg, int $value)
    {
        $sock = @fsockopen($host, $port, $errno, $errstr, 3.0);
        if ($sock === false) { return false; }
        stream_set_timeout($sock, 3);

        $tid  = mt_rand(1, 65535);
        $pdu  = pack('Cnn', 0x06, $reg, $value & 0xFFFF);
        $mbap = pack('nnn', $tid, 0, strlen($pdu) + 1) . chr($unitId);

        fwrite($sock, $mbap . $pdu);
        $resp = @fread($sock, 64);
        fclose($sock);

        return ($resp !== false && strlen($resp) >= 8 && ord($resp[7]) === 0x06);
    }

    private function modbusWriteMultiple(string $host, int $port, int $unitId, int $startReg, array $values)
    {
        $sock = @fsockopen($host, $port, $errno, $errstr, 3.0);
        if ($sock === false) { return false; }
        stream_set_timeout($sock, 3);

        $count     = count($values);
        $byteCount = $count * 2;
        $dataPart  = '';
        foreach ($values as $v) {
            $dataPart .= pack('n', $v & 0xFFFF);
        }
        $tid  = mt_rand(1, 65535);
        $pdu  = pack('CnnC', 0x10, $startReg, $count, $byteCount) . $dataPart;
        $mbap = pack('nnn', $tid, 0, strlen($pdu) + 1) . chr($unitId);

        fwrite($sock, $mbap . $pdu);
        $resp = @fread($sock, 64);
        fclose($sock);

        return ($resp !== false && strlen($resp) >= 8 && ord($resp[7]) === 0x10);
    }

    // -----------------------------------------------------------------------
    // Datentyp-Hilfsfunktionen
    // -----------------------------------------------------------------------

    private function u16(array $regs, int $offset)
    {
        return isset($regs[$offset]) ? ($regs[$offset] & 0xFFFF) : 0;
    }

    private function s16(array $regs, int $offset)
    {
        $v = $this->u16($regs, $offset);
        return $v > 32767 ? $v - 65536 : $v;
    }

    private function u32(array $regs, int $offset)
    {
        return (($this->u16($regs, $offset) << 16) | $this->u16($regs, $offset + 1));
    }

    private function s32(array $regs, int $offset)
    {
        $v = $this->u32($regs, $offset);
        return $v > 2147483647 ? $v - 4294967296 : $v;
    }

    private function readStr(array $regs, int $offset, int $regCount)
    {
        $s = '';
        for ($i = 0; $i < $regCount; $i++) {
            $r  = $this->u16($regs, $offset + $i);
            $s .= chr(($r >> 8) & 0xFF) . chr($r & 0xFF);
        }
        return rtrim($s, "\x00 ");
    }

    private function readFloat(array $regs, int $offset)
    {
        $hi   = $this->u16($regs, $offset);
        $lo   = $this->u16($regs, $offset + 1);
        $raw  = pack('nn', $hi, $lo);
        $vals = unpack('G', $raw);
        return (float)($vals[1] ?? 0.0);
    }

    public function GetConfigurationForm()
    {
        return file_get_contents(__DIR__ . '/form.json');
    }
}
