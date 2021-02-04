-- DROP TABLE IF EXISTS public.dollar_rates;

CREATE TABLE IF NOT EXISTS currency (

   date DATE NOT NULL,

   code VARCHAR(3) NOT NULL,

   rate NUMERIC(10,4) NOT NULL,

 PRIMARY KEY (date, code)

) ENGINE=InnoDB DEFAULT CHARSET=utf8;