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


# 2023-01-05 - refuling
alter table refueling
    add fuel_id int not null;
alter table refueling
    add vehicle_id int not null;

create index vehicle_id
    on refueling (vehicle_id);
create index fuel
    on refueling (fuel_id);

