<?php

// ---------------------------------------------------------------------------
// GoodweRegisterMap — alle Register-Konstanten und Variablen-Definitionen
// ---------------------------------------------------------------------------

class GoodweRegisterMap
{
    const BLOCK_INVERTER = ['start' => 35103, 'count' => 42];
    const BLOCK_BAT1     = ['start' => 35174, 'count' => 18];
    const BLOCK_BAT2     = ['start' => 35262, 'count' => 7];
    const BLOCK_PV_EXT   = ['start' => 35301, 'count' => 41];
    const BLOCK_METER    = ['start' => 36019, 'count' => 39];
    const BLOCK_ARM      = ['start' => 10407, 'count' => 68];
    const BLOCK_ENERGY   = ['start' => 35191, 'count' => 22];
    const BLOCK_METER_E  = ['start' => 36015, 'count' => 4];
    const BLOCK_ERRORS   = ['start' => 32000, 'count' => 17];
    const BLOCK_DEVICE   = ['start' => 35001, 'count' => 27];

    const REG_WORK_MODE         = 47000;
    const REG_FEED_POWER_ENABLE = 47509;
    const REG_FEED_POWER_LIMIT  = 42004;  // >30kW WR: S32, 2 Register
    const REG_EMS_POWER_MODE    = 42000;
    const REG_EMS_POWER_SET     = 42001;  // U32, 2 Register
    const REG_PEAK_SHAVING_PWR  = 47542;  // U32, 2 Register
    const REG_PEAK_SHAVING_SOC  = 47544;
    const REG_SOC_MIN           = 45356;
    const REG_SOC_MAX_CHARGE    = 33518;
    const REG_START_CHARGE_SOC  = 47531;
    const REG_STOP_CHARGE_SOC   = 47532;
    const REG_INTERNET_MODE     = 47017;  // 0=mit Internet, 1=ohne Internet
    const REG_RESTART           = 45220;

    const WORK_MODES = [
        0 => 'Selbstverbrauch',
        1 => 'Inselbetrieb',
        2 => 'Backup',
        3 => 'Wirtschaftlich',
        4 => 'Peak-Shaving',
        5 => 'Erw. Selbstverbrauch',
    ];

    const BAT_MODES = [
        0 => 'Standby',
        1 => 'Laden',
        2 => 'Entladen',
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

    const GROUP_BASE    = 'basis';
    const GROUP_PV      = 'pv';
    const GROUP_GRID    = 'grid';
    const GROUP_BAT1    = 'bat1';
    const GROUP_BAT2    = 'bat2';
    const GROUP_ENERGY  = 'energy';
    const GROUP_METER   = 'meter';
    const GROUP_TEMP    = 'temp';
    const GROUP_ERRORS  = 'errors';
    const GROUP_DEVICE  = 'device';
    const GROUP_CONTROL = 'control';

    // [ident, caption, type(F/I/B/S), profile, scaleFactor, archive, group]
    const VARS_BASE = [
        ['soc',           'SOC [ARM 10472]',              'F', '~Battery.100',      1,    true,  'basis'],
        ['work_mode',     'Betriebsmodus [ARM 10407]',    'I', 'GoodweET.WorkMode', 1,    true,  'basis'],
        ['grid_mode',     'Netzmodus [DSP 35136]',        'I', 'GoodweET.GridMode', 1,    false, 'basis'],
        ['pv_total',      'PV Gesamtleistung [ARM 10412]','F', 'GoodweET.Watt',    1,    true,  'basis'],
        ['ac_power',      'AC Wirkleistung [DSP 35139]',  'F', 'GoodweET.Watt',    1,    true,  'basis'],
        ['bat_total_pwr', 'Bat. Gesamtleistg. [Σ]',      'F', 'GoodweET.Watt',    1,    true,  'basis'],
        ['meter_total',   'Netz Leistung [ARM 10418]',    'F', 'GoodweET.Watt',    1,    true,  'basis'],
        ['connected',     'Verbindung',                   'B', '~Alert.Reversed',  1,    false, 'basis'],
    ];

    const VARS_PV = [
        ['pv1_voltage', 'PV1 Spannung [DSP 35103]',  'F', 'GoodweET.Volt',   10, false, 'pv'],
        ['pv1_current', 'PV1 Strom [DSP 35104]',     'F', 'GoodweET.Ampere', 10, false, 'pv'],
        ['pv1_power',   'PV1 Leistung [DSP 35105]',  'F', 'GoodweET.Watt',    1, true,  'pv'],
        ['pv3_voltage', 'PV3 Spannung [DSP 35111]',  'F', 'GoodweET.Volt',   10, false, 'pv'],
        ['pv3_current', 'PV3 Strom [DSP 35112]',     'F', 'GoodweET.Ampere', 10, false, 'pv'],
        ['pv3_power',   'PV3 Leistung [DSP 35113]',  'F', 'GoodweET.Watt',    1, true,  'pv'],
        ['pv5_voltage', 'PV5 Spannung [DSP 35304]',  'F', 'GoodweET.Volt',   10, false, 'pv'],
        ['pv5_current', 'PV5 Strom [DSP 35305]',     'F', 'GoodweET.Ampere', 10, false, 'pv'],
        ['pv5_power',   'PV5 Leistung [DSP 35341]',  'F', 'GoodweET.Watt',    1, true,  'pv'],
    ];

    const VARS_GRID = [
        ['grid_l1_volt', 'Netz L1 Spannung [DSP 35121]', 'F', 'GoodweET.Volt',   10, false, 'grid'],
        ['grid_l1_curr', 'Netz L1 Strom [DSP 35122]',    'F', 'GoodweET.Ampere', 10, false, 'grid'],
        ['grid_l1_freq', 'Netz L1 Frequenz [DSP 35123]', 'F', 'GoodweET.Hertz', 100, false, 'grid'],
        ['grid_l1_pwr',  'Netz L1 Leistung [DSP 35124]', 'F', 'GoodweET.Watt',   1,  true,  'grid'],
        ['grid_l2_volt', 'Netz L2 Spannung [DSP 35126]', 'F', 'GoodweET.Volt',   10, false, 'grid'],
        ['grid_l2_curr', 'Netz L2 Strom [DSP 35127]',    'F', 'GoodweET.Ampere', 10, false, 'grid'],
        ['grid_l2_freq', 'Netz L2 Frequenz [DSP 35128]', 'F', 'GoodweET.Hertz', 100, false, 'grid'],
        ['grid_l2_pwr',  'Netz L2 Leistung [DSP 35129]', 'F', 'GoodweET.Watt',   1,  true,  'grid'],
        ['grid_l3_volt', 'Netz L3 Spannung [DSP 35131]', 'F', 'GoodweET.Volt',   10, false, 'grid'],
        ['grid_l3_curr', 'Netz L3 Strom [DSP 35132]',    'F', 'GoodweET.Ampere', 10, false, 'grid'],
        ['grid_l3_freq', 'Netz L3 Frequenz [DSP 35133]', 'F', 'GoodweET.Hertz', 100, false, 'grid'],
        ['grid_l3_pwr',  'Netz L3 Leistung [DSP 35134]', 'F', 'GoodweET.Watt',   1,  true,  'grid'],
        ['inv_total',    'Inverter Gesamt [DSP 35137]',   'F', 'GoodweET.Watt',   1,  true,  'grid'],
    ];

    const VARS_BAT1 = [
        ['bat1_volt', 'Bat.1 Spannung [DSP 35180]', 'F', 'GoodweET.Volt',    10, false, 'bat1'],
        ['bat1_curr', 'Bat.1 Strom [DSP 35181]',    'F', 'GoodweET.Ampere',  10, true,  'bat1'],
        ['bat1_pwr',  'Bat.1 Leistung [DSP 35182]', 'F', 'GoodweET.Watt',    1,  true,  'bat1'],
        ['bat1_mode', 'Bat.1 Modus [DSP 35184]',    'I', 'GoodweET.BatMode', 1,  true,  'bat1'],
        ['bat1_soc',  'Bat.1 SOC [ARM 10472]',      'F', '~Battery.100',     1,  true,  'bat1'],
    ];

    const VARS_BAT2 = [
        ['bat2_volt', 'Bat.2 Spannung [DSP 35262]', 'F', 'GoodweET.Volt',    10, false, 'bat2'],
        ['bat2_curr', 'Bat.2 Strom [DSP 35263]',    'F', 'GoodweET.Ampere',  10, true,  'bat2'],
        ['bat2_pwr',  'Bat.2 Leistung [DSP 35264]', 'F', 'GoodweET.Watt',    1,  true,  'bat2'],
        ['bat2_mode', 'Bat.2 Modus [DSP 35266]',    'I', 'GoodweET.BatMode', 1,  true,  'bat2'],
    ];

    const VARS_ENERGY = [
        ['e_pv_day',       'PV Heute [DSP 35193]',           'F', '~Electricity', 10, true,  'energy'],
        ['e_pv_total',     'PV Gesamt [DSP 35191]',          'F', '~Electricity', 10, true,  'energy'],
        ['e_sell_day',     'Einspeisung Heute [DSP 35199]',  'F', '~Electricity', 10, true,  'energy'],
        ['e_sell_total',   'Einspeisung Gesamt [DSP 35200]', 'F', '~Electricity', 10, true,  'energy'],
        ['e_buy_day',      'Bezug Heute [DSP 35202]',        'F', '~Electricity', 10, true,  'energy'],
        ['e_buy_total',    'Bezug Gesamt [DSP 35203]',       'F', '~Electricity', 10, true,  'energy'],
        ['e_load_day',     'Last Heute [DSP 35205]',         'F', '~Electricity', 10, true,  'energy'],
        ['e_load_total',   'Last Gesamt [DSP 35203]',        'F', '~Electricity', 10, true,  'energy'],
        ['e_charge_day',   'Bat. Laden Heute [DSP 35208]',   'F', '~Electricity', 10, true,  'energy'],
        ['e_charge_total', 'Bat. Laden Gesamt [DSP 35206]',  'F', '~Electricity', 10, true,  'energy'],
        ['e_disch_day',    'Bat. Entl. Heute [DSP 35211]',   'F', '~Electricity', 10, true,  'energy'],
        ['e_disch_total',  'Bat. Entl. Gesamt [DSP 35209]',  'F', '~Electricity', 10, true,  'energy'],
        ['work_hours',     'Betriebsstunden [DSP 35197]',    'F', '', 3600, false, 'energy'],
    ];

    const VARS_METER = [
        ['mt_l1_volt', 'GM3000 L1 Spannung [36052]', 'F', 'GoodweET.Volt',   10, false, 'meter'],
        ['mt_l2_volt', 'GM3000 L2 Spannung [36053]', 'F', 'GoodweET.Volt',   10, false, 'meter'],
        ['mt_l3_volt', 'GM3000 L3 Spannung [36054]', 'F', 'GoodweET.Volt',   10, false, 'meter'],
        ['mt_l1_curr', 'GM3000 L1 Strom [36055]',    'F', 'GoodweET.Ampere', 10, false, 'meter'],
        ['mt_l2_curr', 'GM3000 L2 Strom [36056]',    'F', 'GoodweET.Ampere', 10, false, 'meter'],
        ['mt_l3_curr', 'GM3000 L3 Strom [36057]',    'F', 'GoodweET.Ampere', 10, false, 'meter'],
        ['mt_l1_pwr',  'GM3000 L1 Leistung [36019]', 'F', 'GoodweET.Watt',   1,  true,  'meter'],
        ['mt_l2_pwr',  'GM3000 L2 Leistung [36021]', 'F', 'GoodweET.Watt',   1,  true,  'meter'],
        ['mt_l3_pwr',  'GM3000 L3 Leistung [36023]', 'F', 'GoodweET.Watt',   1,  true,  'meter'],
        ['mt_e_sell',  'GM3000 Einspeisung [36015]',  'F', '~Electricity',    1,  true,  'meter'],
        ['mt_e_buy',   'GM3000 Bezug [36017]',        'F', '~Electricity',    1,  true,  'meter'],
    ];

    const VARS_TEMP = [
        ['temp_air',      'Lufttemperatur [DSP 35174]',  'F', '~Temperature', 10, false, 'temp'],
        ['temp_module',   'Modultemperatur [DSP 35175]', 'F', '~Temperature', 10, true,  'temp'],
        ['temp_heatsink', 'Kuehlkoerper [DSP 35176]',   'F', '~Temperature', 10, true,  'temp'],
        ['temp_bms',      'BMS Temperatur [DSP 35368]',  'F', '~Temperature', 10, true,  'temp'],
    ];

    const VARS_ERRORS = [
        ['warn_code',  'Warncode [DSP 32000]',      'I', '', 1, true,  'errors'],
        ['err_msg',    'Fehlercode [DSP 32002]',     'I', '', 1, true,  'errors'],
        ['err_detail', 'Fehler Detail (Bitmaske)',   'S', '', 1, true,  'errors'],
    ];

    const VARS_DEVICE = [
        ['dev_sn',      'Seriennummer [DSP 35003]', 'S', '', 1, false, 'device'],
        ['dev_model',   'Modell [DSP 35011]',       'S', '', 1, false, 'device'],
        ['dev_rated_w', 'Nennleistung [DSP 35001]', 'I', '', 1, false, 'device'],
        ['dev_fw_arm',  'Firmware ARM [DSP 35019]', 'I', '', 1, false, 'device'],
        ['dev_fw_dsp',  'Firmware DSP [DSP 35016]', 'I', '', 1, false, 'device'],
    ];

    const VARS_CONTROL = [
        ['ctl_work_mode',   'Steuermodus [RW 47000]',         'I', 'GoodweET.WorkMode', 1, false, 'control'],
        ['ctl_feed_enable', 'Einspeisegrenze [RW 47509]',     'B', '~Switch',           1, false, 'control'],
        ['ctl_feed_limit',  'Einspeisung Max. W [RW 42004]',  'I', '',                  1, false, 'control'],
        ['ctl_ems_power',   'EMS Leistung W [RW 42001]',      'I', '',                  1, false, 'control'],
        ['ctl_soc_min',     'SOC Min. Entladung [RW 45356]',  'I', 'GoodweET.Percent',  1, false, 'control'],
        ['ctl_soc_max',     'SOC Max. Ladung [RW 33518]',     'I', 'GoodweET.Percent',  1, false, 'control'],
        ['ctl_peak_pwr',    'Peak-Shaving W [RW 47542]',      'I', '',                  1, false, 'control'],
        ['ctl_internet',    'Cloud-Verbindung [RW 47017]',    'B', '~Switch',           1, false, 'control'],
        ['ctl_restart',     'WR Neustart [WO 45220]',         'B', '~Switch',           1, false, 'control'],
    ];
}

// ---------------------------------------------------------------------------
// GoodweET — Hauptmodul
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

        $this->RegisterPropertyBoolean('GroupPV',      true);
        $this->RegisterPropertyBoolean('GroupGrid',    true);
        $this->RegisterPropertyBoolean('GroupBat1',    true);
        $this->RegisterPropertyBoolean('GroupBat2',    true);
        $this->RegisterPropertyBoolean('GroupEnergy',  true);
        $this->RegisterPropertyBoolean('GroupMeter',   true);
        $this->RegisterPropertyBoolean('GroupTemp',    true);
        $this->RegisterPropertyBoolean('GroupErrors',  true);
        $this->RegisterPropertyBoolean('GroupDevice',  true);
        $this->RegisterPropertyBoolean('GroupControl', true);

        $this->RegisterTimer('FastTimer', 0, 'GWET_ReadFast($_IPS[\'TARGET\']);');
        $this->RegisterTimer('SlowTimer', 0, 'GWET_ReadSlow($_IPS[\'TARGET\']);');

        $this->RegisterAttributeBoolean('DeviceInfoRead', false);
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
        $arm     = $this->modbusRead($host, $port, $unitId, 10407, 68);

        $ok = ($inv !== null && $bat1blk !== null && $arm !== null);
        $this->SetVarBool('connected', $ok);

        if (!$ok) {
            $this->SendDebug('ReadFast', 'Modbus-Fehler: keine Antwort', 0);
            return;
        }

        // Basis (ARM)
        $soc      = $this->u16($arm, 65);   // 10472
        $workMode = $this->u16($arm, 0);    // 10407
        $pvTotPwr = $this->u32($arm, 5);    // 10412
        $meterArm = $this->s32($arm, 11);   // 10418

        $this->SetVarFloat('soc', (float)$soc);
        $this->SetVarInt('work_mode', $workMode);
        $this->SetVarFloat('pv_total', (float)$pvTotPwr);
        $this->SetVarFloat('meter_total', (float)$meterArm);

        // PV-Details
        if ($this->ReadPropertyBoolean('GroupPV') && $inv !== null) {
            $this->SetVarFloat('pv1_voltage', $this->u16($inv, 0) / 10.0);
            $this->SetVarFloat('pv1_current', $this->u16($inv, 1) / 10.0);
            $this->SetVarFloat('pv1_power',   (float)$this->u32($inv, 2));
            $this->SetVarFloat('pv3_voltage', $this->u16($inv, 8) / 10.0);
            $this->SetVarFloat('pv3_current', $this->u16($inv, 9) / 10.0);
            $this->SetVarFloat('pv3_power',   (float)$this->u32($inv, 10));
            if ($pvext !== null) {
                $this->SetVarFloat('pv5_voltage', $this->u16($pvext, 3)  / 10.0);
                $this->SetVarFloat('pv5_current', $this->u16($pvext, 4)  / 10.0);
                $this->SetVarFloat('pv5_power',   (float)$this->u16($pvext, 40));
            }
        }

        // Netz R/S/T
        if ($this->ReadPropertyBoolean('GroupGrid') && $inv !== null) {
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
            $this->SetVarInt('grid_mode',     $this->u16($inv, 33));
            $this->SetVarFloat('inv_total',   (float)$this->s32($inv, 34));
            $this->SetVarFloat('ac_power',    (float)$this->s32($inv, 36));
        }

        // Batterie 1
        if ($this->ReadPropertyBoolean('GroupBat1') && $bat1blk !== null) {
            $this->SetVarFloat('bat1_volt', $this->u16($bat1blk, 6)  / 10.0);
            $this->SetVarFloat('bat1_curr', $this->s16($bat1blk, 7)  / 10.0);
            $this->SetVarFloat('bat1_pwr',  (float)$this->s32($bat1blk, 8));
            $this->SetVarInt('bat1_mode',   $this->u16($bat1blk, 10));
            $this->SetVarFloat('bat1_soc',  (float)$soc);
        }

        // Temperaturen
        if ($this->ReadPropertyBoolean('GroupTemp') && $bat1blk !== null) {
            $this->SetVarFloat('temp_air',      $this->s16($bat1blk, 0) / 10.0);
            $this->SetVarFloat('temp_module',   $this->s16($bat1blk, 1) / 10.0);
            $this->SetVarFloat('temp_heatsink', $this->s16($bat1blk, 2) / 10.0);
            $bmsBlk = $this->modbusRead($host, $port, $unitId, 35368, 1);
            if ($bmsBlk !== null) {
                $this->SetVarFloat('temp_bms', $this->s16($bmsBlk, 0) / 10.0);
            }
        }

        // Batterie 2
        if ($this->ReadPropertyBoolean('GroupBat2') && $bat2blk !== null) {
            $this->SetVarFloat('bat2_volt', $this->u16($bat2blk, 0)  / 10.0);
            $this->SetVarFloat('bat2_curr', $this->s16($bat2blk, 1)  / 10.0);
            $this->SetVarFloat('bat2_pwr',  (float)$this->s32($bat2blk, 2));
            $this->SetVarInt('bat2_mode',   $this->u16($bat2blk, 4));
        }

        // Batteriegesamt (berechnet)
        if ($bat1blk !== null) {
            $b1p = $this->s32($bat1blk, 8);
            $b2p = ($bat2blk !== null) ? $this->s32($bat2blk, 2) : 0;
            $this->SetVarFloat('bat_total_pwr', (float)($b1p + $b2p));
        }

        // Smart Meter
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
        $this->SetVarFloat('e_pv_total',     $this->u32($e, 0)  / 10.0);
        $this->SetVarFloat('e_pv_day',       $this->u32($e, 2)  / 10.0);
        $this->SetVarFloat('work_hours',     (float)$this->u32($e, 6));
        $this->SetVarFloat('e_sell_day',     $this->u16($e, 8)  / 10.0);
        $this->SetVarFloat('e_buy_day',      $this->u16($e, 11) / 10.0);
        $this->SetVarFloat('e_load_total',   $this->u32($e, 12) / 10.0);
        $this->SetVarFloat('e_load_day',     $this->u16($e, 14) / 10.0);
        $this->SetVarFloat('e_charge_total', $this->u32($e, 15) / 10.0);
        $this->SetVarFloat('e_charge_day',   $this->u16($e, 17) / 10.0);
        $this->SetVarFloat('e_disch_total',  $this->u32($e, 18) / 10.0);
        $this->SetVarFloat('e_disch_day',    $this->u16($e, 20) / 10.0);

        if ($this->ReadPropertyBoolean('GroupMeter')) {
            $me = $this->modbusRead($host, $port, $unitId, 36015, 4);
            if ($me !== null) {
                $this->SetVarFloat('mt_e_sell', $this->readFloat($me, 0));
                $this->SetVarFloat('mt_e_buy',  $this->readFloat($me, 2));
            }
        }
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

            case 'ctl_feed_enable':
                $val = (bool)$Value ? 1 : 0;
                if ($this->modbusWriteSingle($host, $port, $unitId, GoodweRegisterMap::REG_FEED_POWER_ENABLE, $val)) {
                    $this->SetVarBool('ctl_feed_enable', (bool)$Value);
                }
                break;

            case 'ctl_feed_limit':
                $val = (int)$Value;
                $hi  = ($val >> 16) & 0xFFFF;
                $lo  = $val & 0xFFFF;
                if ($this->modbusWriteMultiple($host, $port, $unitId, GoodweRegisterMap::REG_FEED_POWER_LIMIT, [$hi, $lo])) {
                    $this->SetVarInt('ctl_feed_limit', $val);
                }
                break;

            case 'ctl_ems_power':
                $val = (int)$Value;
                $this->modbusWriteSingle($host, $port, $unitId, GoodweRegisterMap::REG_EMS_POWER_MODE, 1);
                $hi  = ($val >> 16) & 0xFFFF;
                $lo  = $val & 0xFFFF;
                if ($this->modbusWriteMultiple($host, $port, $unitId, GoodweRegisterMap::REG_EMS_POWER_SET, [$hi, $lo])) {
                    $this->SetVarInt('ctl_ems_power', $val);
                }
                break;

            case 'ctl_soc_min':
                $val = max(0, min(100, (int)$Value));
                if ($this->modbusWriteSingle($host, $port, $unitId, GoodweRegisterMap::REG_SOC_MIN, $val)) {
                    $this->SetVarInt('ctl_soc_min', $val);
                }
                break;

            case 'ctl_soc_max':
                $val = max(80, min(100, (int)$Value));
                if ($this->modbusWriteSingle($host, $port, $unitId, GoodweRegisterMap::REG_SOC_MAX_CHARGE, $val)) {
                    $this->SetVarInt('ctl_soc_max', $val);
                }
                break;

            case 'ctl_peak_pwr':
                $val = (int)$Value;
                $hi  = ($val >> 16) & 0xFFFF;
                $lo  = $val & 0xFFFF;
                if ($this->modbusWriteMultiple($host, $port, $unitId, GoodweRegisterMap::REG_PEAK_SHAVING_PWR, [$hi, $lo])) {
                    $this->SetVarInt('ctl_peak_pwr', $val);
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
        // Alte Variablen-Idents (R/S/T) aus früheren Versionen entfernen
        $obsolete = [
            'grid_r_volt','grid_r_curr','grid_r_freq','grid_r_pwr',
            'grid_s_volt','grid_s_curr','grid_s_freq','grid_s_pwr',
            'grid_t_volt','grid_t_curr','grid_t_freq','grid_t_pwr',
            'mt_r_volt','mt_r_curr','mt_r_pwr',
            'mt_s_volt','mt_s_curr','mt_s_pwr',
            'mt_t_volt','mt_t_curr','mt_t_pwr',
        ];
        foreach ($obsolete as $ident) {
            $this->UnregVarIfExists($ident);
        }

        $pos = 0;
        foreach (GoodweRegisterMap::VARS_BASE as $v) {
            $this->RegisterVar($v, $pos++, false);
        }

        $groups = [
            'GroupPV'      => GoodweRegisterMap::VARS_PV,
            'GroupGrid'    => GoodweRegisterMap::VARS_GRID,
            'GroupBat1'    => GoodweRegisterMap::VARS_BAT1,
            'GroupBat2'    => GoodweRegisterMap::VARS_BAT2,
            'GroupEnergy'  => GoodweRegisterMap::VARS_ENERGY,
            'GroupMeter'   => GoodweRegisterMap::VARS_METER,
            'GroupTemp'    => GoodweRegisterMap::VARS_TEMP,
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
        $this->CreateProfile('GoodweET.Watt',    VARIABLETYPE_FLOAT,   ' W',  -30000.0, 30000.0, 1.0,  0);
        $this->CreateProfile('GoodweET.Volt',    VARIABLETYPE_FLOAT,   ' V',       0.0,  1000.0, 0.1,  1);
        $this->CreateProfile('GoodweET.Ampere',  VARIABLETYPE_FLOAT,   ' A',    -200.0,   200.0, 0.1,  1);
        $this->CreateProfile('GoodweET.Hertz',   VARIABLETYPE_FLOAT,   ' Hz',     45.0,    65.0, 0.01, 2);
        $this->CreateProfile('GoodweET.Percent', VARIABLETYPE_INTEGER, ' %',          0,     100, 1,    0);

        if (!IPS_VariableProfileExists('GoodweET.WorkMode')) {
            IPS_CreateVariableProfile('GoodweET.WorkMode', VARIABLETYPE_INTEGER);
            $colors = [0xF5A623, 0x7A8A99, 0x2BB3C0, 0x27D07F, 0xE74C3C, 0xF39C12];
            foreach (GoodweRegisterMap::WORK_MODES as $k => $label) {
                IPS_SetVariableProfileAssociation('GoodweET.WorkMode', $k, $label, '', $colors[$k] ?? 0x7A8A99);
            }
        }

        if (!IPS_VariableProfileExists('GoodweET.BatMode')) {
            IPS_CreateVariableProfile('GoodweET.BatMode', VARIABLETYPE_INTEGER);
            IPS_SetVariableProfileAssociation('GoodweET.BatMode', 0, 'Standby',  '', 0x7A8A99);
            IPS_SetVariableProfileAssociation('GoodweET.BatMode', 1, 'Laden',    '', 0x27D07F);
            IPS_SetVariableProfileAssociation('GoodweET.BatMode', 2, 'Entladen', '', 0xF5A623);
        }

        if (!IPS_VariableProfileExists('GoodweET.GridMode')) {
            IPS_CreateVariableProfile('GoodweET.GridMode', VARIABLETYPE_INTEGER);
            foreach (GoodweRegisterMap::GRID_MODES as $k => $label) {
                IPS_SetVariableProfileAssociation('GoodweET.GridMode', $k, $label, '', 0x7A8A99);
            }
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
