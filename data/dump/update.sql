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

create table moto.vehicle_fuel
(
    vehicle_id int not null,
    fuel_id    int not null,
    constraint vehicle_fuel_pk
        primary key (vehicle_id, fuel_id)
);


