alter table users add name varchar(100) not null after id;

# 2022-12-28 - vehicles
create table moto.fuel
(
    id   int auto_increment
        primary key,
    name varchar(20) null
);

insert into moto.fuel (id, name)
values  (1, 'Бензин'),
        (2, 'Дизел'),
        (3, 'Метан'),
        (4, 'Газ'),
        (5, 'Електричество');


alter table vehicles
    modify reg_nomer varchar(10) null;

alter table vehicles
    drop column fuel;

alter table vehicles
    add color varchar(25) null;

alter table vehicles
    modify odometer varchar(8) null;

alter table vehicles
    add year_manufactured varchar(4) null;

alter table vehicles
    add notes text null;

create table vehicle_fuel
(
    vehicle_id int not null,
    fuel_id    int not null,
    constraint vehicle_fuel_pk
        primary key (vehicle_id, fuel_id)
);

# 2023-01-01 - drivers
alter table drivers
    change vechicle_id vehicle_id int null;


alter table users
    add driver_id int null;


# 2023-01-05 - refueling
alter table refueling
    add fuel_id int not null;
alter table refueling
    add vehicle_id int not null;

create index vehicle_id
    on refueling (vehicle_id);
create index fuel
    on refueling (fuel_id);

# 2023-01-05 - maintenance_schedule
rename table maintanence to maintenance;


alter table maintanence_shadule
    change id_vechicles vehicle_id int null;

alter table maintanence_shadule
    change id_maintenencel maintenance_id int null;

rename table maintanence_shadule to maintenance_schedule;

alter table maintenance_schedule
    change `notify days before` notify_days_before int null;

alter table maintenance_schedule
    change `notify kilometers before` notify_kilometers_before int null;



create index maintenance_schedule_maintenencel_id_index
    on maintenance_schedule (maintenance_id);

create index maintenance_schedule_vechicles_id_index
    on maintenance_schedule (vehicle_id);


# 2023-01-15 - Fuel_report View
create view fuel_report as
    select v.*, f.id as fuel_id, f.name, r.date_refueling, r.odometer as refuling_odometer, r.cost, r.quantity from vehicles v
        JOIN refueling as r on v.id = r.vehicle_id
        JOIN fuel as f on f.id = r.fuel_id;

# 2023-01-15 - assignments fields
alter table assignments
    change drivers_id driver_id int null;

alter table assignments
    change vecihcle_id vehicle_id int null;

# 2023-05-08 - Syntactic error
alter table transire
    change start_odometar start_odometer int null;
alter table transire
    change id_drivers driver_id int null;
alter table transire
    add vehicle_id int null after driver_id;


# 2023-06-09 - Update repair table
alter table repair
    change id_vechicle vehicle_id int null;

alter table repair
    add odometer varchar(8) not null;

alter table repair
    add cost decimal(10, 2) null;

alter table repair
    add invoice_issuer varchar(20) null after notes;


alter table repair
    add invoice_num varchar(20) null after invoice_issuer;

alter table repair
    modify description TEXT null;

alter table repair
    modify notes TEXT null;

# 2023-06-10 - Add new roll to the users
alter table users
    modify role enum ('administrator', 'operator', 'driver') null;


# 2023-07-03 - Add new report view
CREATE VIEW transire_report as
select `t`.`id`                AS `id`,
       `t`.`start_odometer`    AS `start_odometer`,
       `t`.`end_odometer`      AS `end_odometer`,
       `t`.`driver_id`         AS `driver_id`,
       `t`.`vehicle_id`        AS `vehicle_id`,
       `t`.`route`             AS `route`,
       `t`.`date`              AS `date`,
       `d`.`name`              AS `driver_name`,
       `o`.`name`              AS `driver_office_name`,
       `o`.`city`              AS `driver_office_city`,
       `v`.`reg_nomer`         AS `vehicle_reg_nomer`,
       `v`.`model`             AS `vehivle_model`,
       `v`.`color`             AS `vehicle_color`,
       `v`.`year_manufactured` AS `vehicle_year_manufactured`
from `transire` `t`
    join `drivers` `d` on `t`.`driver_id` = `d`.`id`
    join `office` `o` on `d`.`office_id` = `o`.`id`
    join `vehicles` `v` on `t`.`vehicle_id` = `v`.`id`;
