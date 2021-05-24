CREATE TABLE Person (
	Email VARCHAR(320) PRIMARY KEY,
	Name VARCHAR(255) NOT NULL,
    LastName VARCHAR(255) NOT NULL,
	PhoneNumber VARCHAR(16) NOT NULL,
	Password VARCHAR(64) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE Employee (
	Email VARCHAR(320) PRIMARY KEY,
	Salary DECIMAL(8,2) NOT NULL,
	Job VARCHAR(64) NOT NULL,
	DutyStart TIME NOT NULL,
	DutyEnd TIME NOT NULL,
	INDEX idx_i_email(Email),
	FOREIGN KEY (Email) REFERENCES Person(Email)
) ENGINE=InnoDB;

CREATE TABLE Cookie (
	ID INTEGER AUTO_INCREMENT PRIMARY KEY,
	Token VARCHAR(64) NOT NULL,
	Expires DATE NOT NULL,
	Email VARCHAR(320) NOT NULL,
	INDEX idx_cookie_email(Email),
	FOREIGN KEY (Email) REFERENCES Person(Email)
) ENGINE=InnoDB;

CREATE TABLE Content (
	ID INTEGER AUTO_INCREMENT PRIMARY KEY,
	Title VARCHAR(255) NOT NULL,
	Description TEXT NOT NULL,
	Data DATETIME NOT NULL,
	ImageURL VARCHAR(255)
);

CREATE TABLE Tag (
	ID INTEGER AUTO_INCREMENT PRIMARY KEY,
	Name VARCHAR(64) NOT NULL UNIQUE
);

CREATE TABLE ContentTag (
	IDContent INTEGER,
	IDTag INTEGER,
	PRIMARY KEY(IDContent, IDTag),
	INDEX idx_ct_content(IDContent),
	INDEX idx_ct_tag(IDTag),
	FOREIGN KEY (IDContent) REFERENCES Content(ID),
	FOREIGN KEY (IDTag) REFERENCES Tag(ID)
);

CREATE TABLE Favorite (
	Email VARCHAR(320),
	IDContent INTEGER,
	PRIMARY KEY(Email, IDContent),
	INDEX idx_fav_person(Email),
	INDEX idx_fav_content(IDContent),
	FOREIGN KEY (Email) REFERENCES Person(Email),
	FOREIGN KEY (IDContent) REFERENCES Content(ID)
);

CREATE TABLE Room_Types(
	ID INTEGER AUTO_INCREMENT PRIMARY KEY,
	Type VARCHAR(255) NOT NULL,
	Accomodation VARCHAR(255) NOT NULL,
	UNIQUE(Type,Accomodation)
) ENGINE=InnoDB;

CREATE TABLE Rooms(
	RoomNumber VARCHAR(4) PRIMARY KEY,
	RoomType INTEGER NOT NULL,
	PersonNumber INTEGER NOT NULL,
	MatrimonialBed INTEGER NOT NULL,
	SingleBed INTEGER NOT NULL,
	WiFi BOOLEAN NOT NULL,
	WiFiFree BOOLEAN NOT NULL,
	Minibar BOOLEAN  NOT NULL,
	Soundproofing BOOLEAN NOT NULL,
	SwimmingPool BOOLEAN NOT NULL,
	PrivateBathroom BOOLEAN NOT NULL,
	AirConditioning BOOLEAN NOT NULL,
	sqm DECIMAL(5,2) NOT NULL,
	NightlyFee DECIMAL(8,2) NOT NULL,
	Description TEXT NOT NULL,
	INDEX idx_room_type(RoomType),
	FOREIGN KEY (RoomType) REFERENCES Room_Types(ID)
) ENGINE=InnoDB;

CREATE TABLE RoomPhotos(
	ID INTEGER AUTO_INCREMENT PRIMARY KEY,
	RoomNumber VARCHAR(4) NOT NULL,
	PhotoPath VARCHAR(256) NOT NULL,
	INDEX idx_rphotos_roomnum(RoomNumber),
	FOREIGN KEY (RoomNumber) REFERENCES Rooms(RoomNumber)
) ENGINE=InnoDB;

CREATE TABLE Rent(
	id INTEGER AUTO_INCREMENT PRIMARY KEY,
	PersonID VARCHAR(320) NOT NULL,
	RoomNumber VARCHAR(4) NOT NULL,
	NightStay INTEGER NOT NULL,
	NightlyFee DECIMAL(8,2) NOT NULL,
	CheckIn DATE NOT NULL,
	CheckOut DATE NOT NULL, 
	INDEX idx_rent_personid (PersonID),
	INDEX idx_rent_roomnum (RoomNumber),
	FOREIGN KEY (PersonID) REFERENCES Person(Email),
	FOREIGN KEY (RoomNumber) REFERENCES Rooms(RoomNumber)
) ENGINE=InnoDB;

DELIMITER //
CREATE TRIGGER TagUppercase
BEFORE INSERT ON Tag
FOR EACH ROW 
BEGIN
	IF NEW.Name IS NOT NULL THEN 
		SET NEW.Name = UPPER(New.Name);
	END IF;
END //
DELIMITER ;


insert into Content (Title, ImageURL, Data, Description) values 
	(
		'Example 1',
		'resources/images/example.png',
		'2021-03-08 08:00:00',
		'Esempio descrizione'
	),
	(
		'Example 2',
		'resources/images/example.png',
		'2021-03-09 08:00:00',
		'Esempio descrizione'
	),
	(
		'Example 3',
		'resources/images/example.png',
		'2021-03-10 08:00:00',
		'Esempio descrizione'
	),
	(
		'Example 4',
		'resources/images/example.png',
		'2021-03-11 08:00:00',
		'Esempio descrizione'
	),
	(
		'Example 5',
		'resources/images/example.png',
		'2021-03-11 10:00:00',
		'Esempio descrizione'
	),
	(
		'Example 6',
		'resources/images/example.png',
		'2021-03-12 08:00:00',
		'Esempio descrizione'
	),
	(
		'Example 7',
		'resources/images/example.png',
		'2021-03-13 08:00:00',
		'Esempio descrizione'
	),
	(
		'Example 8',
		'resources/images/example.png',
		'2021-03-14 08:00:00',
		'Esempio descrizione'
	),
	(
		'Parcheggio gratuito',
		'resources/images/parcheggio.png',
		'2021-03-18 08:00:00',
		'Dal 20/03/2021 al 31/03/2021 il parcheggio sarà gratuito per chi ha già affittato una camera o per chi affitta una camera in questo intervallo di tempo.'
	),
	(
		'Riapertura della piscina',
		'resources/images/piscina.jpg',
		'2021-03-21 08:00:00',
		'La ristrutturazione della piscina è terminata per tale motivo inauguriamo la nuova riapertura della piscina.'
	),
	(
		'Specialità del giorno',
		'resources/images/pietanza_carbonara.jpg',
		'2021-03-22 08:00:00',
		'Oggi la specialità del giorno è la carbonara.\r\nChi ordina la specialità del giorno riceverà il dessert in omaggio.'
	),
	(
		'Suite Matrimoniale sconto del 50%',
		'resources/images/matrimonial3.jpg',
		'2021-03-22 09:00:00',
		'Solo per oggi affitta una suite matrimoniale per almeno tre notti e ottieni uno sconto del 50%.'
	);
	
insert into Tag (Name) values ('Example'), ('News'), ('Offerta'), ('Ristorazione');

insert into ContentTag (IDContent, IDTag) values (12,3), (11,4), (10,2), (10,1), (9,2), (8,1), (7,1), (6,1),(5,1), (4,1), (3,1), (2,1), (1,1);


insert into Room_Types (Type, Accomodation) values ('Standard', 'Singola');
insert into Room_Types (Type, Accomodation) values ('Standard', 'Doppia singola');
insert into Room_Types (Type, Accomodation) values ('Standard', 'Matrimoniale');
insert into Room_Types (Type, Accomodation) values ('Standard', 'Matrimoniale + Doppia singola');
insert into Room_Types (Type, Accomodation) values ('Superior', 'Singola');
insert into Room_Types (Type, Accomodation) values ('Superior', 'Doppia singola');
insert into Room_Types (Type, Accomodation) values ('Superior', 'Matrimoniale');
insert into Room_Types (Type, Accomodation) values ('Superior', 'Matrimoniale + Singola');
insert into Room_Types (Type, Accomodation) values ('Suite', 'Singola');
insert into Room_Types (Type, Accomodation) values ('Suite', 'Doppia singola');
insert into Room_Types (Type, Accomodation) values ('Suite', 'Matrimoniale');
insert into Room_Types (Type, Accomodation) values ('Suite', 'Quadrupla');

insert into Rooms(RoomNumber, RoomType, PersonNumber, MatrimonialBed, SingleBed, WiFi, WiFiFree, Minibar, Soundproofing, SwimmingPool, PrivateBathroom, AirConditioning, sqm, NightlyFee, Description)
	values	('100A', 1, 1, 0, 1, 1, 0, 0, 1, 0, 1, 1, 28, 50, 'Descrizione'), 
			('101A', 2, 2, 0, 2, 1, 1, 0, 1, 0, 1, 1, 30, 80, 'Descrizione'),
			('102A', 3, 2, 1, 0, 1, 1, 1, 1, 0, 1, 1, 30, 70, 'Descrizione'),
			('103A', 4, 4, 1, 2, 1, 1, 1, 1, 0, 1, 1, 35, 130, 'Lorem ipsum dolor sit amet, consectetur adipisci elit, sed do eiusmod tempor incidunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrum exercitationem ullamco laboriosam, nisi ut aliquid ex ea commodi consequatur. Duis aute irure reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint obcaecat cupiditat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'),
			('200A', 5, 1, 0, 1, 1, 1, 1, 1, 0, 1, 1, 30, 90, 'Lorem ipsum dolor sit amet, consectetur adipisci elit, sed do eiusmod tempor incidunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrum exercitationem ullamco laboriosam, nisi ut aliquid ex ea commodi consequatur. Duis aute irure reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint obcaecat cupiditat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'),
			('201A', 6, 2, 0, 2, 1, 0, 1, 1, 0, 1, 1, 35, 140, 'Lorem ipsum dolor sit amet, consectetur adipisci elit, sed do eiusmod tempor incidunt ut labore et dolore magna aliqua. Ut enim ad minim veniam'),
			('202A', 7, 2, 1, 0, 1, 0, 1, 1, 1, 1, 1, 35, 130, 'Lorem ipsum dolor sit amet, consectetur adipisci elit, sed do eiusmod tempor incidunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrum exercitationem ullamco laboriosam, nisi ut aliquid ex ea commodi consequatur.'),
			('203A', 8, 3, 1, 1, 1, 1, 1, 1, 1, 1, 1, 40, 200, 'Lorem ipsum dolor sit amet, consectetur adipisci elit, sed do eiusmod tempor incidunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrum exercitationem ullamco laboriosam, nisi ut aliquid ex ea commodi consequatur. Duis aute irure reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint obcaecat cupiditat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'),
			('100B', 9, 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 35, 200, 'Lorem ipsum dolor sit amet, consectetur adipisci elit, sed do eiusmod tempor incidunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrum exercitationem ullamco laboriosam, nisi ut aliquid ex ea commodi consequatur. Duis aute irure reprehenderit in voluptate velit esse cillum dolore'),
			('101B',10, 2, 0, 2, 1, 1, 1, 1, 1, 1, 1, 35, 270, 'Lorem ipsum dolor sit amet, consectetur adipisci elit, sed do eiusmod tempor incidunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrum exercitationem ullamco laboriosam, nisi ut aliquid ex ea commodi consequatur. Duis aute irure reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint obcaecat cupiditat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'),
			('200B',11, 2, 1, 0, 1, 1, 1, 1, 1, 1, 1, 35, 260, 'Lorem ipsum dolor sit amet, consectetur adipisci elit, sed do eiusmod tempor incidunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrum exercitationem ullamco laboriosam, nisi ut aliquid ex ea commodi consequatur. Duis aute irure reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.'),
			('201B',12, 4, 1, 2, 1, 1, 1, 1, 1, 1, 1, 50, 400, 'Descrizione........');
			
insert into RoomPhotos (RoomNumber, PhotoPath) values('100A', 'resources/images/single1.jpg');
insert into RoomPhotos (RoomNumber, PhotoPath) values('200A', 'resources/images/single2.jpg');
insert into RoomPhotos (RoomNumber, PhotoPath) values('100B', 'resources/images/single3.jpg');
insert into RoomPhotos (RoomNumber, PhotoPath) values('101A', 'resources/images/double1.jpg');
insert into RoomPhotos (RoomNumber, PhotoPath) values('201A', 'resources/images/double2.jpg');
insert into RoomPhotos (RoomNumber, PhotoPath) values('101B', 'resources/images/double3.jpg');
insert into RoomPhotos (RoomNumber, PhotoPath) values('102A', 'resources/images/matrimonial1.jpg');
insert into RoomPhotos (RoomNumber, PhotoPath) values('202A', 'resources/images/matrimonial2_1.jpg');
insert into RoomPhotos (RoomNumber, PhotoPath) values('202A', 'resources/images/matrimonial2_2.jpg');
insert into RoomPhotos (RoomNumber, PhotoPath) values('200B', 'resources/images/matrimonial3.jpg');
insert into RoomPhotos (RoomNumber, PhotoPath) values('103A', 'resources/images/matrimonial_with_double.jpg');
insert into RoomPhotos (RoomNumber, PhotoPath) values('203A', 'resources/images/matrimonial_with_single.jpg');
insert into RoomPhotos (RoomNumber, PhotoPath) values('201B', 'resources/images/quadruple_1.jpg');
insert into RoomPhotos (RoomNumber, PhotoPath) values('201B', 'resources/images/quadruple_2.jpg');

#Tutte le password equivalgono a Prova123
insert into Person (Email, Name, Lastname, PhoneNumber, Password) values 
	('admin@admin.com', 'Admin', 'Admin', '+390000000', '$2y$10$W5ujgUIOKbOSUF5dBNZk1egUJ0pofNOXgFfFhC8l.fB8duyMrtGPm'),
	('user@user.com', 'Michael', 'Longo', '+390000001', '$2y$10$zuoVN9Q8aotQHyCRz0RopeqGWcWMhrbgbQBL/Mi3d1T1dLUroRp1m');
	
insert into Employee (Email, Salary, Job, DutyStart, DutyEnd) values ('admin@admin.com', '3000', 'ADMIN', '00:00:00', '08:00:00');

		