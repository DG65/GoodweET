<?php

declare(strict_types=1);

/**
 * GoodweRegisterMap
 *
 * Register-Definitionen für GoodWe ET/ETC Hybrid-Wechselrichter.
 * Optimiert für GW29.9k-ET mit Strings an MPPT 1, 3 und 5.
 *
 * Alle Registeradressen basieren auf: GoodWe ARM 745 Modbus Protocol Map, Rev 1.2, 2024-04-24.
 */
class GoodweRegisterMap
{
    // -----------------------------------------------------------------------
    // Lese-Blöcke: zusammenhängende Register, die in einem Request gelesen werden
    // -----------------------------------------------------------------------

    /** Schnell-Polling (5 s): PV + Netz-Inverter */
    const BLOCK_INVERTER = ['start' => 35103, 'count' => 42];

    /** Schnell-Polling (5 s): Temperaturen + Batterie 1 + Status */
    const BLOCK_BAT1 = ['start' => 35174, 'count' => 18];

    /** Schnell-Polling (5 s): Batterie 2 */
    const BLOCK_BAT2 = ['start' => 35262, 'count' => 7];

    /** Schnell-Polling (5 s): PV-Gesamt + PV5 + MPPT-Leistungen */
    const BLOCK_PV_EXT = ['start' => 35301, 'count' => 41];

    /** Schnell-Polling (5 s): Smart Meter GM3000 */
    const BLOCK_METER = ['start' => 36019, 'count' => 39];

    /** Schnell-Polling (5 s): ARM-Zusammenfassung (SOC, Work Mode, Summen) */
    const BLOCK_ARM = ['start' => 10407, 'count' => 68];

    /** Langsam-Polling (5 min): Energie-Tageszähler + Gesamtzähler */
    const BLOCK_ENERGY = ['start' => 35191, 'count' => 22];

    /** Langsam-Polling (5 min): Meter-Energiezähler (Float) */
    const BLOCK_METER_E = ['start' => 36015, 'count' => 4];

    /** Langsam-Polling (5 min): Fehlercodes */
    const BLOCK_ERRORS = ['start' => 32000, 'count' => 17];

    /** Einmalig beim Start: Geräteinformation */
    const BLOCK_DEVICE = ['start' => 35001, 'count' => 27];

    // -----------------------------------------------------------------------
    // Schreib-Register: EMS-Steuerung
    // -----------------------------------------------------------------------

    const REG_WORK_MODE         = 47000;  // RW U16: Betriebsmodus (0-5)
    const REG_FEED_POWER_ENABLE = 47509;  // RW U16: Einspeisebegrenzung ein/aus
    const REG_FEED_POWER_LIMIT  = 42004;  // RW S32 (2 Reg): max. Einspeisung W (>30kW WR!)
    const REG_EMS_POWER_MODE    = 42000;  // RW U16: EMS Modus
    const REG_EMS_POWER_SET     = 42001;  // RW U32 (2 Reg): EMS Leistungsvorgabe W
    const REG_PEAK_SHAVING_PWR  = 47542;  // RW U32 (2 Reg): Peak-Shaving Schwelle W
    const REG_PEAK_SHAVING_SOC  = 47544;  // RW U16: Peak-Shaving Min-SOC %
    const REG_SOC_MIN           = 45356;  // RW U16: Minimaler Entlade-SOC %
    const REG_SOC_MAX_CHARGE    = 33518;  // RW U16: Maximaler Lade-SOC % (80-100)
    const REG_START_CHARGE_SOC  = 47531;  // RW U16: Zwangsladen Start-SOC %
    const REG_STOP_CHARGE_SOC   = 47532;  // RW U16: Zwangsladen Stop-SOC %
    const REG_INTERNET_MODE     = 47017;  // RW U16: 0=mit Internet, 1=ohne Internet
    const REG_RESTART           = 45220;  // WO U16: Neustart (Wert 1)

    // -----------------------------------------------------------------------
    // Work-Mode Assoziationen
    // -----------------------------------------------------------------------

    const WORK_MODES = [
        0 => 'Selbstverbrauch',
        1 => 'Inselbetrieb',
        2 => 'Backup',
        3 => 'Wirtschaftlich',
        4 => 'Peak-Shaving',
        5 => 'Erw. Selbstverbrauch',
    ];

    // -----------------------------------------------------------------------
    // Batterie-Modus Assoziationen (Register 35184 / 35266)
    // -----------------------------------------------------------------------

    const BAT_MODES = [
        0 => 'Standby',
        1 => 'Laden',
        2 => 'Entladen',
    ];

    // -----------------------------------------------------------------------
    // Grid-Modus (Register 35136)
    // -----------------------------------------------------------------------

    const GRID_MODES = [
        0  => 'Warten',
         1 => 'Einspeisung',
         2 => 'Einspeisung: Limit',
         3 => 'Einspeisung: Entsätt.',
         4 => 'Einspeisung: PV-Limit',
         5 => 'Einspeisung: Reaktiv',
         6 => 'Einspeisung: Blindl.',
         7 => 'Einspeisung: Absch.',
         8 => 'Einspeisung: PV-Opt.',
         9 => 'Einspeisung: ECO',
        10 => 'Fehler: HW-Schutz',
        11 => 'Fehler',
        17 => 'Bypass',
        18 => 'Inselbetrieb',
    ];

    // -----------------------------------------------------------------------
    // Variablen-Definitionen nach Gruppen
    // Jeder Eintrag: [ident, caption, type (I/F/S/B), profile, sf, archive, group]
    // type: I=Integer, F=Float, S=String, B=Boolean
    // sf: Skalierungsfaktor (Divisor), 1 = kein Scale
    // -----------------------------------------------------------------------

    const GROUP_BASE      = 'basis';
    const GROUP_PV        = 'pv';
    const GROUP_GRID      = 'grid';
    const GROUP_BAT1      = 'bat1';
    const GROUP_BAT2      = 'bat2';
    const GROUP_ENERGY    = 'energy';
    const GROUP_METER     = 'meter';
    const GROUP_TEMP      = 'temp';
    const GROUP_ERRORS    = 'errors';
    const GROUP_DEVICE    = 'device';
    const GROUP_CONTROL   = 'control';

    // Variablen-Definitionen: Basis-Gruppe (immer aktiv)
    const VARS_BASE = [
        ['soc',          'SOC',               'F', '~Battery.100',     1,    true,  self::GROUP_BASE],
        ['work_mode',    'Betriebsmodus',      'I', 'GoodweET.WorkMode',1,    true,  self::GROUP_BASE],
        ['grid_mode',    'Netzmodus',          'I', 'GoodweET.GridMode',1,    false, self::GROUP_BASE],
        ['pv_total',     'PV Gesamtleistung', 'F', 'GoodweET.Watt',   1,    true,  self::GROUP_BASE],
        ['ac_power',     'AC Wirkleistung',   'F', 'GoodweET.Watt',   1,    true,  self::GROUP_BASE],
        ['bat_total_pwr','Bat. Gesamtleistg.','F', 'GoodweET.Watt',   1,    true,  self::GROUP_BASE],
        ['meter_total',  'Netz Leistung',     'F', 'GoodweET.Watt',   1,    true,  self::GROUP_BASE],
        ['connected',    'Verbindung',        'B', '~Alert.Reversed',  1,    false, self::GROUP_BASE],
    ];

    // PV-Details (MPPT 1, 3, 5)
    const VARS_PV = [
        ['pv1_voltage', 'PV1 Spannung',  'F', 'GoodweET.Volt',  10, false, self::GROUP_PV],
        ['pv1_current', 'PV1 Strom',     'F', 'GoodweET.Ampere',10, false, self::GROUP_PV],
        ['pv1_power',   'PV1 Leistung',  'F', 'GoodweET.Watt',   1, true,  self::GROUP_PV],
        ['pv3_voltage', 'PV3 Spannung',  'F', 'GoodweET.Volt',  10, false, self::GROUP_PV],
        ['pv3_current', 'PV3 Strom',     'F', 'GoodweET.Ampere',10, false, self::GROUP_PV],
        ['pv3_power',   'PV3 Leistung',  'F', 'GoodweET.Watt',   1, true,  self::GROUP_PV],
        ['pv5_voltage', 'PV5 Spannung',  'F', 'GoodweET.Volt',  10, false, self::GROUP_PV],
        ['pv5_current', 'PV5 Strom',     'F', 'GoodweET.Ampere',10, false, self::GROUP_PV],
        ['pv5_power',   'PV5 Leistung',  'F', 'GoodweET.Watt',   1, true,  self::GROUP_PV],
    ];

    // Netz R/S/T
    const VARS_GRID = [
        ['grid_r_volt', 'Netz R Spannung', 'F', 'GoodweET.Volt',   10, false, self::GROUP_GRID],
        ['grid_r_curr', 'Netz R Strom',    'F', 'GoodweET.Ampere', 10, false, self::GROUP_GRID],
        ['grid_r_freq', 'Netz R Frequenz', 'F', 'GoodweET.Hertz', 100, false, self::GROUP_GRID],
        ['grid_r_pwr',  'Netz R Leistung', 'F', 'GoodweET.Watt',   1,  true,  self::GROUP_GRID],
        ['grid_s_volt', 'Netz S Spannung', 'F', 'GoodweET.Volt',   10, false, self::GROUP_GRID],
        ['grid_s_curr', 'Netz S Strom',    'F', 'GoodweET.Ampere', 10, false, self::GROUP_GRID],
        ['grid_s_freq', 'Netz S Frequenz', 'F', 'GoodweET.Hertz', 100, false, self::GROUP_GRID],
        ['grid_s_pwr',  'Netz S Leistung', 'F', 'GoodweET.Watt',   1,  true,  self::GROUP_GRID],
        ['grid_t_volt', 'Netz T Spannung', 'F', 'GoodweET.Volt',   10, false, self::GROUP_GRID],
        ['grid_t_curr', 'Netz T Strom',    'F', 'GoodweET.Ampere', 10, false, self::GROUP_GRID],
        ['grid_t_freq', 'Netz T Frequenz', 'F', 'GoodweET.Hertz', 100, false, self::GROUP_GRID],
        ['grid_t_pwr',  'Netz T Leistung', 'F', 'GoodweET.Watt',   1,  true,  self::GROUP_GRID],
        ['inv_total',   'Inverter Gesamt', 'F', 'GoodweET.Watt',   1,  true,  self::GROUP_GRID],
    ];

    // Batterie 1
    const VARS_BAT1 = [
        ['bat1_volt', 'Bat.1 Spannung', 'F', 'GoodweET.Volt',    10, false, self::GROUP_BAT1],
        ['bat1_curr', 'Bat.1 Strom',    'F', 'GoodweET.Ampere',  10, true,  self::GROUP_BAT1],
        ['bat1_pwr',  'Bat.1 Leistung', 'F', 'GoodweET.Watt',    1,  true,  self::GROUP_BAT1],
        ['bat1_mode', 'Bat.1 Modus',    'I', 'GoodweET.BatMode', 1,  true,  self::GROUP_BAT1],
        ['bat1_soc',  'Bat.1 SOC',      'F', '~Battery.100',     1,  true,  self::GROUP_BAT1],
    ];

    // Batterie 2
    const VARS_BAT2 = [
        ['bat2_volt', 'Bat.2 Spannung', 'F', 'GoodweET.Volt',    10, false, self::GROUP_BAT2],
        ['bat2_curr', 'Bat.2 Strom',    'F', 'GoodweET.Ampere',  10, true,  self::GROUP_BAT2],
        ['bat2_pwr',  'Bat.2 Leistung', 'F', 'GoodweET.Watt',    1,  true,  self::GROUP_BAT2],
        ['bat2_mode', 'Bat.2 Modus',    'I', 'GoodweET.BatMode', 1,  true,  self::GROUP_BAT2],
    ];

    // Energiezähler
    const VARS_ENERGY = [
        ['e_pv_day',       'PV Heute',           'F', '~Electricity', 10, true,  self::GROUP_ENERGY],
        ['e_pv_total',     'PV Gesamt',          'F', '~Electricity', 10, true,  self::GROUP_ENERGY],
        ['e_sell_day',     'Einspeisung Heute',  'F', '~Electricity', 10, true,  self::GROUP_ENERGY],
        ['e_sell_total',   'Einspeisung Gesamt', 'F', '~Electricity', 10, true,  self::GROUP_ENERGY],
        ['e_buy_day',      'Bezug Heute',        'F', '~Electricity', 10, true,  self::GROUP_ENERGY],
        ['e_buy_total',    'Bezug Gesamt',       'F', '~Electricity', 10, true,  self::GROUP_ENERGY],
        ['e_load_day',     'Last Heute',         'F', '~Electricity', 10, true,  self::GROUP_ENERGY],
        ['e_load_total',   'Last Gesamt',        'F', '~Electricity', 10, true,  self::GROUP_ENERGY],
        ['e_charge_day',   'Bat. Laden Heute',   'F', '~Electricity', 10, true,  self::GROUP_ENERGY],
        ['e_charge_total', 'Bat. Laden Gesamt',  'F', '~Electricity', 10, true,  self::GROUP_ENERGY],
        ['e_disch_day',    'Bat. Entl. Heute',   'F', '~Electricity', 10, true,  self::GROUP_ENERGY],
        ['e_disch_total',  'Bat. Entl. Gesamt',  'F', '~Electricity', 10, true,  self::GROUP_ENERGY],
        ['work_hours',     'Betriebsstunden',    'F', '~Duration.hms', 3600, false, self::GROUP_ENERGY],
    ];

    // Smart Meter GM3000
    const VARS_METER = [
        ['mt_r_volt', 'Meter R Spannung', 'F', 'GoodweET.Volt',   10, false, self::GROUP_METER],
        ['mt_s_volt', 'Meter S Spannung', 'F', 'GoodweET.Volt',   10, false, self::GROUP_METER],
        ['mt_t_volt', 'Meter T Spannung', 'F', 'GoodweET.Volt',   10, false, self::GROUP_METER],
        ['mt_r_curr', 'Meter R Strom',    'F', 'GoodweET.Ampere', 10, false, self::GROUP_METER],
        ['mt_s_curr', 'Meter S Strom',    'F', 'GoodweET.Ampere', 10, false, self::GROUP_METER],
        ['mt_t_curr', 'Meter T Strom',    'F', 'GoodweET.Ampere', 10, false, self::GROUP_METER],
        ['mt_r_pwr',  'Meter R Leistung', 'F', 'GoodweET.Watt',   1,  true,  self::GROUP_METER],
        ['mt_s_pwr',  'Meter S Leistung', 'F', 'GoodweET.Watt',   1,  true,  self::GROUP_METER],
        ['mt_t_pwr',  'Meter T Leistung', 'F', 'GoodweET.Watt',   1,  true,  self::GROUP_METER],
        ['mt_e_sell', 'Meter Einsp. Ges.','F', '~Electricity',    1,  true,  self::GROUP_METER],
        ['mt_e_buy',  'Meter Bezug Ges.', 'F', '~Electricity',    1,  true,  self::GROUP_METER],
    ];

    // Temperaturen
    const VARS_TEMP = [
        ['temp_air',      'Lufttemperatur',   'F', '~Temperature', 10, false, self::GROUP_TEMP],
        ['temp_module',   'Modultemperatur',  'F', '~Temperature', 10, true,  self::GROUP_TEMP],
        ['temp_heatsink', 'Kühlkörper',       'F', '~Temperature', 10, true,  self::GROUP_TEMP],
        ['temp_bms',      'BMS Temperatur',   'F', '~Temperature', 10, true,  self::GROUP_TEMP],
    ];

    // Fehlercodes
    const VARS_ERRORS = [
        ['warn_code',  'Warncode',      'I', '', 1, true,  self::GROUP_ERRORS],
        ['err_msg',    'Fehlercode',    'I', '', 1, true,  self::GROUP_ERRORS],
        ['err_detail', 'Fehler Detail', 'S', '', 1, true,  self::GROUP_ERRORS],
    ];

    // Geräteinformation (einmalig)
    const VARS_DEVICE = [
        ['dev_sn',      'Seriennummer',     'S', '', 1, false, self::GROUP_DEVICE],
        ['dev_model',   'Modell',           'S', '', 1, false, self::GROUP_DEVICE],
        ['dev_rated_w', 'Nennleistung',     'I', '', 1, false, self::GROUP_DEVICE],
        ['dev_fw_arm',  'Firmware ARM',     'I', '', 1, false, self::GROUP_DEVICE],
        ['dev_fw_dsp',  'Firmware DSP',     'I', '', 1, false, self::GROUP_DEVICE],
    ];

    // Steuerungs-Variablen (mit RequestAction)
    const VARS_CONTROL = [
        ['ctl_work_mode',     'Steuermodus',          'I', 'GoodweET.WorkMode', 1, false, self::GROUP_CONTROL],
        ['ctl_feed_enable',   'Einspeisegrenze',       'B', '~Switch',           1, false, self::GROUP_CONTROL],
        ['ctl_feed_limit',    'Einspeisung Max. (W)',  'I', '',                  1, false, self::GROUP_CONTROL],
        ['ctl_ems_power',     'EMS Leistung (W)',      'I', '',                  1, false, self::GROUP_CONTROL],
        ['ctl_soc_min',       'SOC Min. Entladung',    'I', 'GoodweET.Percent',  1, false, self::GROUP_CONTROL],
        ['ctl_soc_max',       'SOC Max. Ladung',       'I', 'GoodweET.Percent',  1, false, self::GROUP_CONTROL],
        ['ctl_peak_pwr',      'Peak-Shaving (W)',      'I', '',                  1, false, self::GROUP_CONTROL],
        ['ctl_internet',      'Cloud-Verbindung',      'B', '~Switch',           1, false, self::GROUP_CONTROL],
        ['ctl_restart',       'WR Neustart',           'B', '~Switch',           1, false, self::GROUP_CONTROL],
    ];
}
