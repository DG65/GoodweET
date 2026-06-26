<?php

declare(strict_types=1);

require_once __DIR__ . '/RegisterMap.php';

/**
 * GoodweET
 *
 * IP-Symcon Modul für GoodWe ET/ETC Hybrid-Wechselrichter via Modbus TCP.
 * Direkte TCP-Verbindung, kein übergeordnetes Gateway erforderlich.
 *
 * Unterstützt: GW29.9k-ET, optimiert für 3 Strings an MPPT 1/3/5,
 * 2 Batterietürme (Lynx Home D), Smart Meter GM3000.
 */
class GoodweET extends IPSModule
{
    private const MODULE_GUID = '{1C4B7E2A-8F3D-5A9C-4E1B-7D2F9A3C6E8B}';

    public function Create(): void
    {
        parent::Create();

        // Verbindungs-Eigenschaften
        $this->RegisterPropertyString('Host', '');
        $this->RegisterPropertyInteger('Port', 502);
        $this->RegisterPropertyInteger('UnitId', 247);

        // Polling-Intervalle
        $this->RegisterPropertyInteger('IntervalFast', 5);
        $this->RegisterPropertyInteger('IntervalSlow', 300);

        // Gruppen-Aktivierung
        $this->RegisterPropertyBoolean('GroupPV',     true);
        $this->RegisterPropertyBoolean('GroupGrid',   true);
        $this->RegisterPropertyBoolean('GroupBat1',   true);
        $this->RegisterPropertyBoolean('GroupBat2',   true);
        $this->RegisterPropertyBoolean('GroupEnergy', true);
        $this->RegisterPropertyBoolean('GroupMeter',  true);
        $this->RegisterPropertyBoolean('GroupTemp',   true);
        $this->RegisterPropertyBoolean('GroupErrors', true);
        $this->RegisterPropertyBoolean('GroupDevice', true);
        $this->RegisterPropertyBoolean('GroupControl',true);

        // Timer
        $this->RegisterTimer('FastTimer', 0, 'GWET_ReadFast($_IPS[\'TARGET\']);');
        $this->RegisterTimer('SlowTimer', 0, 'GWET_ReadSlow($_IPS[\'TARGET\']);');

        // Attribut für einmalig gelesene Gerätedaten
        $this->RegisterAttributeBoolean('DeviceInfoRead', false);
    }

    public function Destroy(): void
    {
        parent::Destroy();
    }

    public function ApplyChanges(): void
    {
        parent::ApplyChanges();

        $this->CreateProfiles();
        $this->RegisterVariables();

        $host = $this->ReadPropertyString('Host');
        if ($host === '') {
            $this->SetStatus(101);
            $this->SetTimerInterval('FastTimer', 0);
            $this->SetTimerInterval('SlowTimer', 0);
            return;
        }

        $this->SetTimerInterval('FastTimer', $this->ReadPropertyInteger('IntervalFast') * 1000);
        $this->SetTimerInterval('SlowTimer', $this->ReadPropertyInteger('IntervalSlow') * 1000);

        // Gerätedaten beim nächsten Fast-Zyklus neu lesen
        $this->WriteAttributeBoolean('DeviceInfoRead', false);

        $this->SetStatus(102);
    }

    // -----------------------------------------------------------------------
    // Öffentliche Timer-Methoden
    // -----------------------------------------------------------------------

    public function ReadFast(): void
    {
        if (!$this->ReadAttributeBoolean('DeviceInfoRead') && $this->ReadPropertyBoolean('GroupDevice')) {
            $this->ReadDeviceInfo();
        }
        $this->ReadInverterData();
    }

    public function ReadSlow(): void
    {
        $this->ReadEnergyData();
        $this->ReadErrorData();
    }

    // -----------------------------------------------------------------------
    // Interne Lese-Methoden
    // -----------------------------------------------------------------------

    private function ReadInverterData(): void
    {
        $host   = $this->ReadPropertyString('Host');
        $port   = $this->ReadPropertyInteger('Port');
        $unitId = $this->ReadPropertyInteger('UnitId');

        // Block: Inverter (35103-35144)
        $inv = $this->modbusRead($host, $port, $unitId, 35103, 42);
        // Block: Batterie 1 + Temperaturen (35174-35191)
        $bat1blk = $this->modbusRead($host, $port, $unitId, 35174, 18);
        // Block: Batterie 2 (35262-35268)
        $bat2blk = $this->modbusRead($host, $port, $unitId, 35262, 7);
        // Block: PV-Ext + MPPT (35301-35341)
        $pvext = $this->modbusRead($host, $port, $unitId, 35301, 41);
        // Block: Meter GM3000 (36019-36057)
        $meter = $this->modbusRead($host, $port, $unitId, 36019, 39);
        // Block: ARM-Zusammenfassung (10407-10474)
        $arm = $this->modbusRead($host, $port, $unitId, 10407, 68);

        $ok = ($inv !== null && $bat1blk !== null && $arm !== null);
        $this->SetVarBool('connected', $ok);

        if (!$ok) {
            $this->SendDebug('ReadFast', 'Modbus-Fehler: keine Antwort', 0);
            return;
        }

        // --- Basis: ARM-Zusammenfassung ---
        // Offset relativ zu 10407
        $soc      = $this->u16($arm, 65);          // 10472 = offset 65
        $workMode = $this->u16($arm, 0);            // 10407 = offset 0
        $pvTotPwr = $this->u32($arm, 5);            // 10412 = offset 5

        $this->SetVarFloat('soc', (float)$soc);
        $this->SetVarInt('work_mode', $workMode);
        $this->SetVarFloat('pv_total', (float)$pvTotPwr);

        // Netz-Gesamtleistung (Meter Power) aus ARM: 10418 = offset 11
        $meterPwrArm = $this->s32($arm, 11);
        $this->SetVarFloat('meter_total', (float)$meterPwrArm);

        // --- PV-Details (Block inv, Start 35103) ---
        if ($this->ReadPropertyBoolean('GroupPV') && $inv !== null) {
            // PV1: 35103(V), 35104(I), 35105-35106(P U32)
            $this->SetVarFloat('pv1_voltage', $this->u16($inv, 0)  / 10.0);
            $this->SetVarFloat('pv1_current', $this->u16($inv, 1)  / 10.0);
            $this->SetVarFloat('pv1_power',   (float)$this->u32($inv, 2));
            // PV3: 35111(V), 35112(I), 35113-35114(P U32)
            $this->SetVarFloat('pv3_voltage', $this->u16($inv, 8)  / 10.0);
            $this->SetVarFloat('pv3_current', $this->u16($inv, 9)  / 10.0);
            $this->SetVarFloat('pv3_power',   (float)$this->u32($inv, 10));

            // PV5: 35304(V), 35305(I) aus pvext-Block (Start 35301, offset 3/4)
            // MPPT5-Leistung: 35341 aus pvext-Block (offset 40)
            if ($pvext !== null) {
                $this->SetVarFloat('pv5_voltage', $this->u16($pvext, 3) / 10.0);
                $this->SetVarFloat('pv5_current', $this->u16($pvext, 4) / 10.0);
                $this->SetVarFloat('pv5_power',   (float)$this->u16($pvext, 40));
            }
        }

        // --- Netz / Inverter R/S/T (Block inv, Start 35103) ---
        if ($this->ReadPropertyBoolean('GroupGrid') && $inv !== null) {
            // 35121(R-V)=offset 18, 35122(R-I)=19, 35123(R-f)=20, 35124-35125(R-P S32)=21
            $this->SetVarFloat('grid_r_volt', $this->u16($inv, 18) / 10.0);
            $this->SetVarFloat('grid_r_curr', $this->u16($inv, 19) / 10.0);
            $this->SetVarFloat('grid_r_freq', $this->u16($inv, 20) / 100.0);
            $this->SetVarFloat('grid_r_pwr',  (float)$this->s32($inv, 21));
            // S: 35126=23, 35127=24, 35128=25, 35129-35130=26
            $this->SetVarFloat('grid_s_volt', $this->u16($inv, 23) / 10.0);
            $this->SetVarFloat('grid_s_curr', $this->u16($inv, 24) / 10.0);
            $this->SetVarFloat('grid_s_freq', $this->u16($inv, 25) / 100.0);
            $this->SetVarFloat('grid_s_pwr',  (float)$this->s32($inv, 26));
            // T: 35131=28, 35132=29, 35133=30, 35134-35135=31
            $this->SetVarFloat('grid_t_volt', $this->u16($inv, 28) / 10.0);
            $this->SetVarFloat('grid_t_curr', $this->u16($inv, 29) / 10.0);
            $this->SetVarFloat('grid_t_freq', $this->u16($inv, 30) / 100.0);
            $this->SetVarFloat('grid_t_pwr',  (float)$this->s32($inv, 31));
            // Grid Mode: 35136 = offset 33
            $this->SetVarInt('grid_mode', $this->u16($inv, 33));
            // Total Inverter: 35137-35138 = offset 34
            $this->SetVarFloat('inv_total', (float)$this->s32($inv, 34));
            // AC Active Power: 35139-35140 = offset 36
            $this->SetVarFloat('ac_power', (float)$this->s32($inv, 36));
        }

        // --- Batterie 1 (Block bat1blk, Start 35174) ---
        if ($this->ReadPropertyBoolean('GroupBat1') && $bat1blk !== null) {
            // 35174=Luft(0), 35175=Modul(1), 35176=Heatsink(2)
            // 35180=Bat1V(6), 35181=Bat1I(7 S16), 35182-35183=Bat1P(8 S32), 35184=Bat1Mode(10)
            $this->SetVarFloat('bat1_volt', $this->u16($bat1blk, 6)  / 10.0);
            $this->SetVarFloat('bat1_curr', $this->s16($bat1blk, 7)  / 10.0);
            $this->SetVarFloat('bat1_pwr',  (float)$this->s32($bat1blk, 8));
            $this->SetVarInt('bat1_mode',   $this->u16($bat1blk, 10));
            // BAT1 SOC aus ARM: 10472 = offset 65
            $this->SetVarFloat('bat1_soc', (float)$soc);
        }

        // --- Temperaturen ---
        if ($this->ReadPropertyBoolean('GroupTemp') && $bat1blk !== null) {
            $this->SetVarFloat('temp_air',      $this->s16($bat1blk, 0) / 10.0);
            $this->SetVarFloat('temp_module',   $this->s16($bat1blk, 1) / 10.0);
            $this->SetVarFloat('temp_heatsink', $this->s16($bat1blk, 2) / 10.0);
            // BMS Temp: 35368 = nicht im bat1blk-Block -> eigener Read wenn aktiviert
            // Offset 35368-35174 = 194 -> außerhalb des Blocks, separater Read
            $bmsBlk = $this->modbusRead($host, $port, $unitId, 35368, 1);
            if ($bmsBlk !== null) {
                $this->SetVarFloat('temp_bms', $this->s16($bmsBlk, 0) / 10.0);
            }
        }

        // --- Batterie 2 ---
        if ($this->ReadPropertyBoolean('GroupBat2') && $bat2blk !== null) {
            // 35262=Bat2V(0), 35263=Bat2I(1 S16), 35264-35265=Bat2P(2 S32), 35266=Bat2Mode(4)
            $this->SetVarFloat('bat2_volt', $this->u16($bat2blk, 0)  / 10.0);
            $this->SetVarFloat('bat2_curr', $this->s16($bat2blk, 1)  / 10.0);
            $this->SetVarFloat('bat2_pwr',  (float)$this->s32($bat2blk, 2));
            $this->SetVarInt('bat2_mode',   $this->u16($bat2blk, 4));
        }

        // --- Batteriegesamt (berechnet) ---
        if ($bat1blk !== null) {
            $b1p = $this->s32($bat1blk, 8);
            $b2p = ($bat2blk !== null) ? $this->s32($bat2blk, 2) : 0;
            $this->SetVarFloat('bat_total_pwr', (float)($b1p + $b2p));
        }

        // --- Smart Meter GM3000 ---
        if ($this->ReadPropertyBoolean('GroupMeter') && $meter !== null) {
            // 36019-36020=R-Pwr(0 S32), 36021-36022=S-Pwr(2), 36023-36024=T-Pwr(4)
            $this->SetVarFloat('mt_r_pwr', (float)$this->s32($meter, 0));
            $this->SetVarFloat('mt_s_pwr', (float)$this->s32($meter, 2));
            $this->SetVarFloat('mt_t_pwr', (float)$this->s32($meter, 4));
            // 36052=R-V(33), 36053=S-V(34), 36054=T-V(35)
            $this->SetVarFloat('mt_r_volt', $this->u16($meter, 33) / 10.0);
            $this->SetVarFloat('mt_s_volt', $this->u16($meter, 34) / 10.0);
            $this->SetVarFloat('mt_t_volt', $this->u16($meter, 35) / 10.0);
            // 36055=R-I(36), 36056=S-I(37), 36057=T-I(38)
            $this->SetVarFloat('mt_r_curr', $this->u16($meter, 36) / 10.0);
            $this->SetVarFloat('mt_s_curr', $this->u16($meter, 37) / 10.0);
            $this->SetVarFloat('mt_t_curr', $this->u16($meter, 38) / 10.0);
        }
    }

    private function ReadEnergyData(): void
    {
        if (!$this->ReadPropertyBoolean('GroupEnergy')) {
            return;
        }
        $host   = $this->ReadPropertyString('Host');
        $port   = $this->ReadPropertyInteger('Port');
        $unitId = $this->ReadPropertyInteger('UnitId');

        // Block 35191-35212
        $e = $this->modbusRead($host, $port, $unitId, 35191, 22);
        if ($e === null) {
            return;
        }
        // 35191-35192: PV Total (U32, /10 kWh) = offset 0
        $this->SetVarFloat('e_pv_total',   $this->u32($e, 0)  / 10.0);
        // 35193-35194: PV Day (U32, /10 kWh) = offset 2
        $this->SetVarFloat('e_pv_day',     $this->u32($e, 2)  / 10.0);
        // 35197-35198: Work Hours Total (U32, h) = offset 6
        $this->SetVarFloat('work_hours',   (float)$this->u32($e, 6));
        // 35199: Sell Day (U16, /10) = offset 8
        $this->SetVarFloat('e_sell_day',   $this->u16($e, 8)  / 10.0);
        // 35202: Buy Day (U16, /10) = offset 11
        $this->SetVarFloat('e_buy_day',    $this->u16($e, 11) / 10.0);
        // 35203-35204: Load Total (U32, /10) = offset 12
        $this->SetVarFloat('e_load_total', $this->u32($e, 12) / 10.0);
        // 35205: Load Day (U16, /10) = offset 14
        $this->SetVarFloat('e_load_day',   $this->u16($e, 14) / 10.0);
        // 35206-35207: Bat Charge Total (U32, /10) = offset 15
        $this->SetVarFloat('e_charge_total', $this->u32($e, 15) / 10.0);
        // 35208: Bat Charge Day (U16, /10) = offset 17
        $this->SetVarFloat('e_charge_day',   $this->u16($e, 17) / 10.0);
        // 35209-35210: Bat Discharge Total (U32, /10) = offset 18
        $this->SetVarFloat('e_disch_total',  $this->u32($e, 18) / 10.0);
        // 35211: Bat Discharge Day (U16, /10) = offset 20
        $this->SetVarFloat('e_disch_day',    $this->u16($e, 20) / 10.0);

        // Meter Energie (Float, 36015-36018)
        if ($this->ReadPropertyBoolean('GroupMeter')) {
            $me = $this->modbusRead($host, $port, $unitId, 36015, 4);
            if ($me !== null) {
                // 36015-36016: Sell Total (Float)
                $this->SetVarFloat('mt_e_sell', $this->readFloat($me, 0));
                // 36017-36018: Buy Total (Float)
                $this->SetVarFloat('mt_e_buy',  $this->readFloat($me, 2));
            }
        }

        // Sell/Buy Gesamt aus 45226/45231 (falls Meter-Summen fehlen)
        // Diese sind schreibbar und werden als Backup nicht extra gelesen
    }

    private function ReadErrorData(): void
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

        // Fehler-Details als lesbare Bitmasken-Beschreibung
        $utility = $this->u16($err, 0);
        $detail  = [];
        if ($utility & 0x01)   { $detail[] = 'Netz-Überspannung'; }
        if ($utility & 0x02)   { $detail[] = 'Netz-Unterspannung'; }
        if ($utility & 0x04)   { $detail[] = 'Netz-Überfrequenz'; }
        if ($utility & 0x08)   { $detail[] = 'Netz-Unterfrequenz'; }
        if ($utility & 0x10)   { $detail[] = 'Netz-Überstrom'; }
        $sys = $this->u16($err, 2);
        if ($sys & 0x01)       { $detail[] = 'Systemfehler 1'; }
        $this->SetVarStr('err_detail', empty($detail) ? 'OK' : implode(', ', $detail));
    }

    private function ReadDeviceInfo(): void
    {
        $host   = $this->ReadPropertyString('Host');
        $port   = $this->ReadPropertyInteger('Port');
        $unitId = $this->ReadPropertyInteger('UnitId');

        $dev = $this->modbusRead($host, $port, $unitId, 35001, 27);
        if ($dev === null) {
            return;
        }
        // 35001: Rated Power (U16) = offset 0
        $this->SetVarInt('dev_rated_w', $this->u16($dev, 0));
        // 35003-35010: SN (STR 8 regs) = offset 2
        $this->SetVarStr('dev_sn', $this->readStr($dev, 2, 8));
        // 35011-35015: Model (STR 5 regs) = offset 10
        $this->SetVarStr('dev_model', $this->readStr($dev, 10, 5));
        // 35016: FW DSP Master = offset 15
        $this->SetVarInt('dev_fw_dsp', $this->u16($dev, 15));
        // 35019: FW ARM = offset 18
        $this->SetVarInt('dev_fw_arm', $this->u16($dev, 18));

        $this->WriteAttributeBoolean('DeviceInfoRead', true);
    }

    // -----------------------------------------------------------------------
    // Schreib-Aktionen (EMS-Steuerung)
    // -----------------------------------------------------------------------

    public function RequestAction(string $Ident, mixed $Value): void
    {
        $host   = $this->ReadPropertyString('Host');
        $port   = $this->ReadPropertyInteger('Port');
        $unitId = $this->ReadPropertyInteger('UnitId');

        switch ($Ident) {
            case 'ctl_work_mode':
                $val = (int)$Value;
                if ($val < 0 || $val > 5) {
                    return;
                }
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
                // GW29.9k-ET > 30kW: Register 42004 (S32, 2 Register)
                $val = (int)$Value;
                $hi  = ($val >> 16) & 0xFFFF;
                $lo  = $val & 0xFFFF;
                if ($this->modbusWriteMultiple($host, $port, $unitId, GoodweRegisterMap::REG_FEED_POWER_LIMIT, [$hi, $lo])) {
                    $this->SetVarInt('ctl_feed_limit', $val);
                }
                break;

            case 'ctl_ems_power':
                // EMS Power Set: 42001 (U32, 2 Register)
                $val = (int)$Value;
                // Modus auf 1 setzen (EMS-Steuerung aktiv), dann Leistung
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
                // 47017: 0 = mit Internet, 1 = ohne Internet
                $val = (bool)$Value ? 0 : 1;
                if ($this->modbusWriteSingle($host, $port, $unitId, GoodweRegisterMap::REG_INTERNET_MODE, $val)) {
                    $this->SetVarBool('ctl_internet', (bool)$Value);
                }
                break;

            case 'ctl_restart':
                if ((bool)$Value) {
                    $this->modbusWriteSingle($host, $port, $unitId, GoodweRegisterMap::REG_RESTART, 1);
                    // Variable sofort zurücksetzen
                    IPS_Sleep(500);
                    $this->SetVarBool('ctl_restart', false);
                }
                break;
        }
    }

    // -----------------------------------------------------------------------
    // Variablen-Registrierung
    // -----------------------------------------------------------------------

    private function RegisterVariables(): void
    {
        $pos = 0;

        // Basis (immer)
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
                    $isCtrl = ($v[6] === GoodweRegisterMap::GROUP_CONTROL);
                    $this->RegisterVar($v, $pos++, $isCtrl);
                } else {
                    $this->UnregVarIfExists($v[0]);
                }
            }
        }
    }

    private function RegisterVar(array $def, int $pos, bool $withAction): void
    {
        [$ident, $caption, $type, $profile, , $archive] = $def;

        $vid = @$this->GetIDForIdent($ident);

        switch ($type) {
            case 'F':
                if (!$vid) {
                    $vid = $this->RegisterVariableFloat($ident, $caption, $profile, $pos);
                }
                break;
            case 'I':
                if (!$vid) {
                    $vid = $this->RegisterVariableInteger($ident, $caption, $profile, $pos);
                }
                break;
            case 'B':
                if (!$vid) {
                    $vid = $this->RegisterVariableBoolean($ident, $caption, $profile, $pos);
                }
                break;
            case 'S':
                if (!$vid) {
                    $vid = $this->RegisterVariableString($ident, $caption, $profile, $pos);
                }
                break;
        }

        if ($vid && $withAction) {
            $this->EnableAction($ident);
        }

        if ($vid && $archive) {
            $this->SetArchive($vid);
        }
    }

    private function UnregVarIfExists(string $ident): void
    {
        if (@$this->GetIDForIdent($ident)) {
            $this->UnregisterVariable($ident);
        }
    }

    private function SetArchive(int $vid): void
    {
        $archiveIDs = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}');
        if (count($archiveIDs) > 0) {
            AC_SetLoggingStatus($archiveIDs[0], $vid, true);
            AC_SetAggregationType($archiveIDs[0], $vid, 0);
        }
    }

    // -----------------------------------------------------------------------
    // Variable setzen (nur bei Änderung)
    // -----------------------------------------------------------------------

    private function SetVarFloat(string $ident, float $value): void
    {
        $vid = @$this->GetIDForIdent($ident);
        if ($vid) {
            SetValueFloat($vid, $value);
        }
    }

    private function SetVarInt(string $ident, int $value): void
    {
        $vid = @$this->GetIDForIdent($ident);
        if ($vid) {
            SetValueInteger($vid, $value);
        }
    }

    private function SetVarBool(string $ident, bool $value): void
    {
        $vid = @$this->GetIDForIdent($ident);
        if ($vid) {
            SetValueBoolean($vid, $value);
        }
    }

    private function SetVarStr(string $ident, string $value): void
    {
        $vid = @$this->GetIDForIdent($ident);
        if ($vid) {
            SetValueString($vid, $value);
        }
    }

    // -----------------------------------------------------------------------
    // Profile erstellen
    // -----------------------------------------------------------------------

    private function CreateProfiles(): void
    {
        $this->CreateProfile('GoodweET.Watt', VARIABLETYPE_FLOAT, ' W', -30000.0, 30000.0, 1.0, 0);
        $this->CreateProfile('GoodweET.Volt', VARIABLETYPE_FLOAT, ' V', 0.0, 1000.0, 0.1, 1);
        $this->CreateProfile('GoodweET.Ampere', VARIABLETYPE_FLOAT, ' A', -200.0, 200.0, 0.1, 1);
        $this->CreateProfile('GoodweET.Hertz', VARIABLETYPE_FLOAT, ' Hz', 45.0, 65.0, 0.01, 2);
        $this->CreateProfile('GoodweET.Percent', VARIABLETYPE_INTEGER, ' %', 0, 100, 1, 0);

        // Work Mode
        if (!IPS_VariableProfileExists('GoodweET.WorkMode')) {
            IPS_CreateVariableProfile('GoodweET.WorkMode', VARIABLETYPE_INTEGER);
            foreach (GoodweRegisterMap::WORK_MODES as $k => $label) {
                $color = [0xF5A623, 0x7A8A99, 0x2BB3C0, 0x27D07F, 0xE74C3C, 0xF39C12][$k] ?? 0x7A8A99;
                IPS_SetVariableProfileAssociation('GoodweET.WorkMode', $k, $label, '', $color);
            }
        }

        // Batterie-Modus
        if (!IPS_VariableProfileExists('GoodweET.BatMode')) {
            IPS_CreateVariableProfile('GoodweET.BatMode', VARIABLETYPE_INTEGER);
            IPS_SetVariableProfileAssociation('GoodweET.BatMode', 0, 'Standby',   '', 0x7A8A99);
            IPS_SetVariableProfileAssociation('GoodweET.BatMode', 1, 'Laden',     '', 0x27D07F);
            IPS_SetVariableProfileAssociation('GoodweET.BatMode', 2, 'Entladen',  '', 0xF5A623);
        }

        // Grid-Modus
        if (!IPS_VariableProfileExists('GoodweET.GridMode')) {
            IPS_CreateVariableProfile('GoodweET.GridMode', VARIABLETYPE_INTEGER);
            foreach (GoodweRegisterMap::GRID_MODES as $k => $label) {
                IPS_SetVariableProfileAssociation('GoodweET.GridMode', $k, $label, '', 0x7A8A99);
            }
        }
    }

    private function CreateProfile(string $name, int $type, string $suffix, float $min, float $max, float $step, int $digits): void
    {
        if (!IPS_VariableProfileExists($name)) {
            IPS_CreateVariableProfile($name, $type);
        }
        IPS_SetVariableProfileDigits($name, $digits);
        IPS_SetVariableProfileText($name, '', $suffix);
        if ($type === VARIABLETYPE_FLOAT || $type === VARIABLETYPE_INTEGER) {
            IPS_SetVariableProfileValues($name, $min, $max, $step);
        }
    }

    // -----------------------------------------------------------------------
    // Modbus TCP
    // -----------------------------------------------------------------------

    private function modbusRead(string $host, int $port, int $unitId, int $startReg, int $count): ?array
    {
        $sock = @fsockopen($host, $port, $errno, $errstr, 3.0);
        if ($sock === false) {
            $this->SendDebug('Modbus', "Verbindung fehlgeschlagen: $errstr ($errno)", 0);
            return null;
        }
        stream_set_timeout($sock, 3);

        $tid = mt_rand(1, 65535);
        // PDU: FC03 + Startadresse + Anzahl
        $pdu = pack('Cnn', 0x03, $startReg, $count);
        // MBAP Header: TransID + ProtID(0) + Länge(PDU+UnitID) + UnitID
        $mbap = pack('nnn', $tid, 0, strlen($pdu) + 1) . chr($unitId);

        fwrite($sock, $mbap . $pdu);

        $response = '';
        $deadline = microtime(true) + 3.0;
        while (microtime(true) < $deadline) {
            $chunk = @fread($sock, 512);
            if ($chunk === false || $chunk === '') {
                break;
            }
            $response .= $chunk;
            // Prüfe ob vollständige Antwort: MBAP(6) + FC(1) + ByteCount(1) + Data
            if (strlen($response) >= 9) {
                $byteCount = ord($response[8]);
                if (strlen($response) >= 9 + $byteCount) {
                    break;
                }
            }
        }
        fclose($sock);

        if (strlen($response) < 9) {
            $this->SendDebug('Modbus', "Antwort zu kurz: " . strlen($response) . " Bytes", 0);
            return null;
        }

        $fc = ord($response[7]);
        if ($fc & 0x80) {
            $errCode = ord($response[8]);
            $this->SendDebug('Modbus', "Exception FC=$fc ErrCode=$errCode Reg=$startReg", 0);
            return null;
        }
        if ($fc !== 0x03) {
            return null;
        }

        $byteCount = ord($response[8]);
        $data      = substr($response, 9, $byteCount);

        if (strlen($data) < $byteCount) {
            return null;
        }

        $regs = [];
        for ($i = 0; $i < $count && ($i * 2 + 1) < strlen($data); $i++) {
            $regs[$i] = (ord($data[$i * 2]) << 8) | ord($data[$i * 2 + 1]);
        }
        return $regs;
    }

    private function modbusWriteSingle(string $host, int $port, int $unitId, int $reg, int $value): bool
    {
        $sock = @fsockopen($host, $port, $errno, $errstr, 3.0);
        if ($sock === false) {
            return false;
        }
        stream_set_timeout($sock, 3);

        $tid  = mt_rand(1, 65535);
        $pdu  = pack('Cnn', 0x06, $reg, $value & 0xFFFF);
        $mbap = pack('nnn', $tid, 0, strlen($pdu) + 1) . chr($unitId);

        fwrite($sock, $mbap . $pdu);
        $resp = @fread($sock, 64);
        fclose($sock);

        return ($resp !== false && strlen($resp) >= 8 && ord($resp[7]) === 0x06);
    }

    private function modbusWriteMultiple(string $host, int $port, int $unitId, int $startReg, array $values): bool
    {
        $sock = @fsockopen($host, $port, $errno, $errstr, 3.0);
        if ($sock === false) {
            return false;
        }
        stream_set_timeout($sock, 3);

        $count    = count($values);
        $byteCount = $count * 2;
        $dataPart = '';
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

    private function u16(array $regs, int $offset): int
    {
        return isset($regs[$offset]) ? ($regs[$offset] & 0xFFFF) : 0;
    }

    private function s16(array $regs, int $offset): int
    {
        $v = $this->u16($regs, $offset);
        return $v > 32767 ? $v - 65536 : $v;
    }

    private function u32(array $regs, int $offset): int
    {
        return (($this->u16($regs, $offset) << 16) | $this->u16($regs, $offset + 1));
    }

    private function s32(array $regs, int $offset): int
    {
        $v = $this->u32($regs, $offset);
        return $v > 2147483647 ? $v - 4294967296 : $v;
    }

    private function readStr(array $regs, int $offset, int $regCount): string
    {
        $s = '';
        for ($i = 0; $i < $regCount; $i++) {
            $r = $this->u16($regs, $offset + $i);
            $s .= chr(($r >> 8) & 0xFF) . chr($r & 0xFF);
        }
        return rtrim($s, "\x00 ");
    }

    private function readFloat(array $regs, int $offset): float
    {
        // IEEE 754 Float aus 2 x U16 (Big-Endian)
        $hi   = $this->u16($regs, $offset);
        $lo   = $this->u16($regs, $offset + 1);
        $raw  = pack('nn', $hi, $lo);
        $vals = unpack('G', $raw);
        return (float)($vals[1] ?? 0.0);
    }

    // -----------------------------------------------------------------------
    // Konfigurationsmaske
    // -----------------------------------------------------------------------

    public function GetConfigurationForm(): string
    {
        return file_get_contents(__DIR__ . '/form.json');
    }
}
