# administratorsky ucet
insert into address (address_id) values (null);
select @address_id:=last_insert_id();
insert into user (firstname,lastname,admin,disabled,address,username,password,validated) values ('Petr','Kos','Y','N',@address_id,'admin',md5('admin'),now());
