CREATE TABLE fdcategory(
    id INT NOT NULL AUTO_INCREMENT,
    created_at DATETIME,
    updated_at DATETIME,
    deleted_at DATETIME,

    categoryname VARCHAR(100) NOT NULL,
    categoryphoto VARCHAR(50),
    
    PRIMARY KEY(id)
);

CREATE TABLE fddishes (
    id INT NOT NULL AUTO_INCREMENT,
    created_at DATETIME,
    updated_at DATETIME,
    deleted_at DATETIME,

    dishname VARCHAR(100) NOT NULL,
    dishcategory INT NOT NULL,
    dishdescription VARCHAR(1000),
    dishingredients VARCHAR(1000),
    dishphoto VARCHAR(50),

    PRIMARY KEY(id),
    FOREIGN KEY(dishcategory) REFERENCES fdcategory(id)
);

CREATE TABLE fdgallery (
    id INT NOT NULL AUTO_INCREMENT,
    created_at DATETIME,
    updated_at DATETIME,
    deleted_at DATETIME,
    
    dish INT NOT NULL,
    dishphoto VARCHAR(50),

    PRIMARY KEY(id),
    FOREIGN KEY(dish) REFERENCES fddishes(id)
);

CREATE TABLE fdprises(
    id INT NOT NULL AUTO_INCREMENT,
    created_at DATETIME,
    updated_at DATETIME,
    deleted_at DATETIME,

    dish INT NOT NULL,
    price DECIMAL (10,2),
    productcost DECIMAL (10,2),
    active BOOLEAN,

    PRIMARY KEY(id),
    FOREIGN KEY(dish) REFERENCES fddishes(id)
);

CREATE TABLE fdcomments(
    id INT NOT NULL AUTO_INCREMENT,
    created_at DATETIME,
    updated_at DATETIME,
    deleted_at DATETIME,

    dish INT NOT NULL,
    comment VARCHAR(500),
    rate SMALLINT,

    PRIMARY KEY(id),
    FOREIGN KEY(dish) REFERENCES fddishes(id)
);

CREATE TABLE fdclients(
    id INT NOT NULL AUTO_INCREMENT,
    created_at DATETIME,
    updated_at DATETIME,
    deleted_at DATETIME,

    login VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(50),
    clientname VARCHAR(100),  
    clientsphonenumber VARCHAR(20),
    clientsaddress VARCHAR(200),

    PRIMARY KEY(id)
);


CREATE TABLE fdorderstatus(
    id INT NOT NULL AUTO_INCREMENT,
    created_at DATETIME,
    updated_at DATETIME,
    deleted_at DATETIME,

    statusname VARCHAR(20) NOT NULL,

    PRIMARY KEY(id)
);

CREATE TABLE fdorders(
    id INT NOT NULL AUTO_INCREMENT,
    created_at DATETIME,
    updated_at DATETIME,
    deleted_at DATETIME,

    orderdate DATE NOT NULL,
    client INT NOT NULL,
    deliveryaddress VARCHAR(200),
    status INT,
    totalcost DECIMAL (10,2),
    ordercomment VARCHAR(200),

    PRIMARY KEY(id),
    FOREIGN KEY(client) REFERENCES fdclients(id),
    FOREIGN KEY(status) REFERENCES fdorderstatus(id)
);

CREATE TABLE fdorderlist(
    id INT NOT NULL AUTO_INCREMENT,
    created_at DATETIME,
    updated_at DATETIME,
    deleted_at DATETIME,

    dish INT NOT NULL,
    price DECIMAL (10,2),
    quantity SMALLINT,
    cost  DECIMAL (10,2),
    ordernumber INT NOT NULL,

    PRIMARY KEY(id),
    FOREIGN KEY(dish) REFERENCES fddishes(id),
    FOREIGN KEY(ordernumber) REFERENCES fdorders(id)
);