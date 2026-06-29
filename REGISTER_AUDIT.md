# GoodweET — Register-Audit (Stand 2026-06-10)

Diff: bestehende `GoodweRegisterMap` (PHP-Modul) gegen die 9 validierten
Live-Modbus-Configs (SmartMeter, PV, WR, WR-Batterien, BMS1, BMS2, Backup,
WR-Zähler, EMS). Maßgeblich = die Live-Config (sie steuert/liest nachweislich).

## Verdikt in einem Satz
Die DSP-Blöcke (WR-AC, Batterie-Leistung, Energiezähler) stimmen überein.
Divergent/fehlend sind: **die Steuerbank**, die **SOC/SOH/BMS-Quellen**, die
**PV-Leistungsquelle**, die **Netz-Gesamtleistung** und der **Insel/Backup-Block**.

## 4 kritische Divergenzen (müssen angeglichen werden)

| Thema | Modul aktuell | Live-Config (maßgeblich) | Aktion |
|-------|---------------|--------------------------|--------|
| **EMS-Steuerung** | 42000 Mode / 42001 Power | **47511 Mode / 47512 Power** | Modul auf 47511/47512 umstellen |
| **EMS-Enable** | — (nicht gesetzt) | **47505 = 2** | einmalig setzen (verifizieren ob nötig) |
| **Export-Limit** | 42004 | **47509 enable / 47510 limit** | auf 47509/47510 umstellen |
| **Batterie-SOC** | ARM 10472 (kombiniert) | **BMS 47908 (Bat1) / 47926 (Bat2)** | pro String aus BMS lesen |

Die ARM-Aggregatblöcke (10407+) nutzt das Modul als Schnellübersicht; deine
Live-Configs lesen stattdessen dedizierte Register. Empfehlung: dedizierte
Register sind die Wahrheit (genauer pro Gerät), ARM nur optional als Overview.

## Quellen-Divergenzen (gleicher Wert, anderes Register)

| Größe | Modul | Live-Config | Empfehlung |
|-------|-------|-------------|------------|
| PV-Gesamtleistung | ARM 10412 | **35301** (P Total) | 35301 |
| PV-Leistung je Strang | 35105/09/13… (6 Strings) | **35337/38/39** (3 MPPT) | MPPT-Ebene 35337-39 (passt zu 3 physischen MPPTs) |
| PV-Strom | im inv-Block je String | **35345/46/47** (MPPT) + 35104.. (String) | MPPT-Ströme ergänzen |
| Netz-Gesamtleistung | ARM 10418 | **36025** (SmartMeter) | 36025 (dedizierter NAP-Zähler) |

## Fehlt im Modul — ergänzen (für Kachel & EMS nötig)

| Block | Register | Wofür |
|-------|----------|-------|
| **SOH** | 47909 (Bat1) / 47927 (Bat2) | Kachel SOH-Anzeige |
| **BMS Temp** | 47910/47928 + Pack 37003/39001 + Zelle hoch/niedrig 37020/21, 39018/19 | Batterie-Detail |
| **Zellspannungen** | 37022/37023 (Bat1), 39020/39021 (Bat2) mV | Batterie-Detail |
| **BMS SOC/Strom/Spg** | 47906-47908 / 47924-47926 | präzise pro String |
| **Min-SOC online/offline** | 45356/45358 (Bat1), 45381/45383 (Bat2), RW | lesbar+schreibbar |
| **Netzfrequenz** | 36014 (SmartMeter) | Anzeige |
| **SmartMeter Status** | 50090 Typ / 50091 Ort / 50094 Verbindung | Diagnose |
| **Insel/Backup** | 35145-35169 (Backup L1-3 + Gesamt 35169), Status 45252, Autostart 45253 | **Netztrenn-/Inselstatus-Anzeige** |
| **Diagnose-Status** | 35220 | WR-Diagnose |
| **Extra-Temps** | DC/DC 35600, MPPT 35601, STS 35602 | optional |
| **RISO** | 35365 (×10) | optional (Isolationswiderstand) |

## Insel-/Netztrenn-Erkennung (für Kachel-Wunsch)
Signal kombinieren: `grid_mode` (DSP 35136) Wert **18 = Inselbetrieb / 17 =
Bypass** UND `Backup Leistung Gesamt` 35169 > 0. Beides zusammen = WR läuft
netzgetrennt im Backup/Insel-Modus.

## Vorhanden, aber bewusst NICHT genutzt (Realtime-Steuerung)
Zeitfenster 47515-47530 und Force-Charge-SoC 47531/47532 werden NICHT
geschrieben (Entscheidung: EMS feuert realtime, siehe EMS-Repo
INVERTER_ABSTRACTION.md). Bleiben unangetastet.

## Bugs im aktuellen Modul
- `e_buy_total` wird in `ReadEnergyData()` nie gesetzt (nur `e_buy_day`).
- `VARS_ENERGY`: `e_load_total` und `e_buy_total` referenzieren beide 35203.
- Tile: Bat2-SOC-Ring zeigt den kombinierten ARM-SOC statt 47926 (fällt mit
  der SOC-Umstellung oben weg).
- **Register 47512 Wertebereich:** Die GoodWe-Register-Doku nennt `[0, 10000]` —
  das gilt für kleine WR und ist für den GW29.9k-ET FALSCH. Korrekt: `[0, 34500]`
  (= 50 A SLS-Äquivalent, ~34641 W gedeckelt). Profil `Goodwe.WattEMS` in EMS.json
  ist bereits richtig (Max 34500); das Modul muss diesen Wert führen, nicht 10000.

## Konsolidierte Ident-Liste (Ziel-Datenmodell, Auszug)
Lesen: soc, soh, bat1_soc, bat1_soh, bat2_soc, bat2_soh, bat1_pwr, bat2_pwr,
bat1_temp, bat2_temp, bat1_cell_v_max/min, pv_total, pv_mppt1/2/3, grid_total,
grid_freq, wr_total, work_mode, grid_mode, island_active, backup_total,
e_pv_day, e_sell_day, e_buy_day, e_charge_day, e_disch_day, e_load_day.
Steuern (Nature ApplySetpoint → Register): ctl_ems_mode 47511, ctl_ems_power
47512, ctl_ems_enable 47505, ctl_export_enable 47509, ctl_export_limit 47510,
ctl_work_mode 47000, ctl_internet 47017, ctl_restart 45220.
```
