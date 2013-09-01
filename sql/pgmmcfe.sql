--
-- PostgreSQL database dump
--

-- Dumped from database version 8.4.17
-- Dumped by pg_dump version 9.2.0
-- Started on 2013-08-10 20:30:50

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

--
-- TOC entry 1927 (class 1262 OID 149742)
-- Name: pgmmcfe; Type: DATABASE; Schema: -; Owner: -
--

CREATE DATABASE pgmmcfe WITH TEMPLATE = template0 ENCODING = 'UTF8' LC_COLLATE = 'en_US.UTF-8' LC_CTYPE = 'en_US.UTF-8';


\connect pgmmcfe

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

--
-- TOC entry 178 (class 1255 OID 149743)
-- Name: get_currentblock(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION get_currentblock() RETURNS bigint
    LANGUAGE sql
    AS $$
  SELECT "value"::bigint FROM settings where setting = 'currentblock';
$$;


SET default_with_oids = false;

--
-- TOC entry 140 (class 1259 OID 149744)
-- Name: accountbalance; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE accountbalance (
    id bigint NOT NULL,
    userid bigint NOT NULL,
    balance numeric,
    sendaddress character varying DEFAULT ''::character varying,
    paid numeric DEFAULT (0)::numeric,
    threshold numeric(7,2) DEFAULT 0.00
);


--
-- TOC entry 141 (class 1259 OID 149753)
-- Name: accountbalance_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE accountbalance_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 1930 (class 0 OID 0)
-- Dependencies: 141
-- Name: accountbalance_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE accountbalance_id_seq OWNED BY accountbalance.id;


--
-- TOC entry 142 (class 1259 OID 149755)
-- Name: accountbalance_userid_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE accountbalance_userid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 1931 (class 0 OID 0)
-- Dependencies: 142
-- Name: accountbalance_userid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE accountbalance_userid_seq OWNED BY accountbalance.userid;


--
-- TOC entry 165 (class 1259 OID 149922)
-- Name: invite_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE invite_tokens (
    id bigint NOT NULL,
    token character varying,
    token_from character varying,
    token_to character varying,
    date_generated timestamp without time zone DEFAULT now(),
    date_redeemed timestamp without time zone
);


--
-- TOC entry 164 (class 1259 OID 149920)
-- Name: invite_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE invite_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 1932 (class 0 OID 0)
-- Dependencies: 164
-- Name: invite_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE invite_tokens_id_seq OWNED BY invite_tokens.id;


--
-- TOC entry 143 (class 1259 OID 149757)
-- Name: ledger; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE ledger (
    id bigint NOT NULL,
    userid integer NOT NULL,
    transtype character varying,
    sendaddress character varying DEFAULT ''::character varying,
    amount character varying DEFAULT '0'::character varying,
    feeamount character varying DEFAULT '0'::character varying,
    assocblock integer DEFAULT 0,
    "timestamp" timestamp without time zone DEFAULT now() NOT NULL
);


--
-- TOC entry 144 (class 1259 OID 149768)
-- Name: ledger_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE ledger_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 1933 (class 0 OID 0)
-- Dependencies: 144
-- Name: ledger_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE ledger_id_seq OWNED BY ledger.id;


--
-- TOC entry 145 (class 1259 OID 149770)
-- Name: networkblocks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE networkblocks (
    id bigint NOT NULL,
    blocknumber integer NOT NULL,
    "timestamp" integer NOT NULL,
    accountaddress character varying,
    confirms integer NOT NULL,
    difficulty numeric NOT NULL,
    reward_amount numeric
);


--
-- TOC entry 146 (class 1259 OID 149776)
-- Name: networkblocks_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE networkblocks_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 1934 (class 0 OID 0)
-- Dependencies: 146
-- Name: networkblocks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE networkblocks_id_seq OWNED BY networkblocks.id;


--
-- TOC entry 147 (class 1259 OID 149778)
-- Name: pool_worker; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE pool_worker (
    id bigint NOT NULL,
    associateduserid integer NOT NULL,
    username character varying,
    password character varying,
    active boolean DEFAULT false,
    hashrate integer DEFAULT 0,
    was_active boolean DEFAULT false NOT NULL,
    notify_down boolean DEFAULT false NOT NULL,
    email character varying,
    sms character varying
);


--
-- TOC entry 148 (class 1259 OID 149785)
-- Name: pool_worker_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE pool_worker_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 1935 (class 0 OID 0)
-- Dependencies: 148
-- Name: pool_worker_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE pool_worker_id_seq OWNED BY pool_worker.id;


--
-- TOC entry 149 (class 1259 OID 149787)
-- Name: settings; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE settings (
    setting character varying NOT NULL,
    value character varying
);


--
-- TOC entry 150 (class 1259 OID 149793)
-- Name: shares; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE shares (
    id bigint NOT NULL,
    rem_host character varying NOT NULL,
    username character varying NOT NULL,
    our_result character varying NOT NULL,
    upstream_result character varying,
    reason character varying,
    solution character varying NOT NULL,
    "time" timestamp without time zone DEFAULT now() NOT NULL,
    current_block bigint DEFAULT get_currentblock()
);


--
-- TOC entry 151 (class 1259 OID 149801)
-- Name: shares_counted; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE shares_counted (
    id bigint NOT NULL,
    blocknumber integer NOT NULL,
    userid integer NOT NULL,
    count integer NOT NULL,
    invalid integer DEFAULT 0 NOT NULL,
    counted integer DEFAULT 1 NOT NULL,
    score numeric
);


--
-- TOC entry 152 (class 1259 OID 149809)
-- Name: shares_counted_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE shares_counted_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 1936 (class 0 OID 0)
-- Dependencies: 152
-- Name: shares_counted_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE shares_counted_id_seq OWNED BY shares_counted.id;


--
-- TOC entry 153 (class 1259 OID 149811)
-- Name: shares_history; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE shares_history (
    id bigint NOT NULL,
    counted integer NOT NULL,
    blocknumber integer NOT NULL,
    rem_host character varying NOT NULL,
    username character varying NOT NULL,
    our_result character varying NOT NULL,
    upstream_result character varying,
    reason character varying,
    solution character varying NOT NULL,
    "time" timestamp without time zone DEFAULT now() NOT NULL,
    score numeric
);


--
-- TOC entry 154 (class 1259 OID 149818)
-- Name: shares_history_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE shares_history_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 1937 (class 0 OID 0)
-- Dependencies: 154
-- Name: shares_history_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE shares_history_id_seq OWNED BY shares_history.id;


--
-- TOC entry 155 (class 1259 OID 149820)
-- Name: shares_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE shares_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 1938 (class 0 OID 0)
-- Dependencies: 155
-- Name: shares_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE shares_id_seq OWNED BY shares.id;


--
-- TOC entry 156 (class 1259 OID 149822)
-- Name: shares_uncounted; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE shares_uncounted (
    id bigint NOT NULL,
    blocknumber integer NOT NULL,
    userid integer NOT NULL,
    count integer NOT NULL,
    invalid integer DEFAULT 0 NOT NULL,
    counted integer DEFAULT 0 NOT NULL,
    score numeric
);


--
-- TOC entry 157 (class 1259 OID 149830)
-- Name: shares_uncounted_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE shares_uncounted_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 1939 (class 0 OID 0)
-- Dependencies: 157
-- Name: shares_uncounted_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE shares_uncounted_id_seq OWNED BY shares_uncounted.id;


--
-- TOC entry 158 (class 1259 OID 149832)
-- Name: userhashrates; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE userhashrates (
    id bigint NOT NULL,
    "timestamp" timestamp without time zone DEFAULT now() NOT NULL,
    userid integer NOT NULL,
    hashrate integer DEFAULT 0 NOT NULL
);


--
-- TOC entry 159 (class 1259 OID 149837)
-- Name: userhashrates_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE userhashrates_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 1940 (class 0 OID 0)
-- Dependencies: 159
-- Name: userhashrates_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE userhashrates_id_seq OWNED BY userhashrates.id;


--
-- TOC entry 160 (class 1259 OID 149839)
-- Name: webusers; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE webusers (
    id bigint NOT NULL,
    admin integer NOT NULL,
    username character varying NOT NULL,
    pass character varying NOT NULL,
    email character varying NOT NULL,
    emailauthpin character varying NOT NULL,
    secret character varying NOT NULL,
    loggedip character varying NOT NULL,
    sessiontimeoutstamp integer NOT NULL,
    accountlocked integer NOT NULL,
    accountfailedattempts integer NOT NULL,
    pin character varying NOT NULL,
    share_count integer,
    stale_share_count integer,
    shares_this_round integer,
    api_key character varying,
    activeemail integer,
    hashrate integer,
    donate_percent numeric DEFAULT 0,
    round_estimate numeric DEFAULT 0,
    account_type integer DEFAULT 0 NOT NULL,
    tz character varying
);


--
-- TOC entry 161 (class 1259 OID 149848)
-- Name: webusers_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE webusers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 1941 (class 0 OID 0)
-- Dependencies: 161
-- Name: webusers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE webusers_id_seq OWNED BY webusers.id;


--
-- TOC entry 162 (class 1259 OID 149850)
-- Name: winning_shares; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE winning_shares (
    id bigint NOT NULL,
    blocknumber integer NOT NULL,
    username character varying NOT NULL,
    sharecount integer DEFAULT 0 NOT NULL
);


--
-- TOC entry 163 (class 1259 OID 149857)
-- Name: winning_shares_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE winning_shares_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 1942 (class 0 OID 0)
-- Dependencies: 163
-- Name: winning_shares_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE winning_shares_id_seq OWNED BY winning_shares.id;


--
-- TOC entry 1858 (class 2604 OID 149859)
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY accountbalance ALTER COLUMN id SET DEFAULT nextval('accountbalance_id_seq'::regclass);


--
-- TOC entry 1859 (class 2604 OID 149860)
-- Name: userid; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY accountbalance ALTER COLUMN userid SET DEFAULT nextval('accountbalance_userid_seq'::regclass);


--
-- TOC entry 1893 (class 2604 OID 149925)
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY invite_tokens ALTER COLUMN id SET DEFAULT nextval('invite_tokens_id_seq'::regclass);


--
-- TOC entry 1866 (class 2604 OID 149861)
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY ledger ALTER COLUMN id SET DEFAULT nextval('ledger_id_seq'::regclass);


--
-- TOC entry 1867 (class 2604 OID 149862)
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY networkblocks ALTER COLUMN id SET DEFAULT nextval('networkblocks_id_seq'::regclass);


--
-- TOC entry 1869 (class 2604 OID 149863)
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY pool_worker ALTER COLUMN id SET DEFAULT nextval('pool_worker_id_seq'::regclass);


--
-- TOC entry 1875 (class 2604 OID 149864)
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY shares ALTER COLUMN id SET DEFAULT nextval('shares_id_seq'::regclass);


--
-- TOC entry 1878 (class 2604 OID 149865)
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY shares_counted ALTER COLUMN id SET DEFAULT nextval('shares_counted_id_seq'::regclass);


--
-- TOC entry 1880 (class 2604 OID 149866)
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY shares_history ALTER COLUMN id SET DEFAULT nextval('shares_history_id_seq'::regclass);


--
-- TOC entry 1883 (class 2604 OID 149867)
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY shares_uncounted ALTER COLUMN id SET DEFAULT nextval('shares_uncounted_id_seq'::regclass);


--
-- TOC entry 1886 (class 2604 OID 149868)
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY userhashrates ALTER COLUMN id SET DEFAULT nextval('userhashrates_id_seq'::regclass);


--
-- TOC entry 1890 (class 2604 OID 149869)
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY webusers ALTER COLUMN id SET DEFAULT nextval('webusers_id_seq'::regclass);


--
-- TOC entry 1892 (class 2604 OID 149870)
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY winning_shares ALTER COLUMN id SET DEFAULT nextval('winning_shares_id_seq'::regclass);


--
-- TOC entry 1896 (class 2606 OID 149872)
-- Name: accountbalance_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY accountbalance
    ADD CONSTRAINT accountbalance_pkey PRIMARY KEY (id);


--
-- TOC entry 1922 (class 2606 OID 149931)
-- Name: invite_token_pk; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY invite_tokens
    ADD CONSTRAINT invite_token_pk PRIMARY KEY (id);


--
-- TOC entry 1900 (class 2606 OID 149874)
-- Name: ledger_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY ledger
    ADD CONSTRAINT ledger_pkey PRIMARY KEY (id);


--
-- TOC entry 1902 (class 2606 OID 149876)
-- Name: networkblocks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY networkblocks
    ADD CONSTRAINT networkblocks_pkey PRIMARY KEY (id);


--
-- TOC entry 1904 (class 2606 OID 149878)
-- Name: pool_worker_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY pool_worker
    ADD CONSTRAINT pool_worker_pkey PRIMARY KEY (id);


--
-- TOC entry 1906 (class 2606 OID 149880)
-- Name: settings_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY settings
    ADD CONSTRAINT settings_pkey PRIMARY KEY (setting);


--
-- TOC entry 1910 (class 2606 OID 149882)
-- Name: shares_counted_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY shares_counted
    ADD CONSTRAINT shares_counted_pkey PRIMARY KEY (id);


--
-- TOC entry 1912 (class 2606 OID 149884)
-- Name: shares_history_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY shares_history
    ADD CONSTRAINT shares_history_pkey PRIMARY KEY (id);


--
-- TOC entry 1908 (class 2606 OID 149886)
-- Name: shares_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY shares
    ADD CONSTRAINT shares_pkey PRIMARY KEY (id);


--
-- TOC entry 1914 (class 2606 OID 149888)
-- Name: shares_uncounted_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY shares_uncounted
    ADD CONSTRAINT shares_uncounted_pkey PRIMARY KEY (id);


--
-- TOC entry 1916 (class 2606 OID 149890)
-- Name: userhashrates_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY userhashrates
    ADD CONSTRAINT userhashrates_pkey PRIMARY KEY (id);


--
-- TOC entry 1898 (class 2606 OID 149892)
-- Name: userid_uc; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY accountbalance
    ADD CONSTRAINT userid_uc UNIQUE (userid);


--
-- TOC entry 1918 (class 2606 OID 149894)
-- Name: webusers_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY webusers
    ADD CONSTRAINT webusers_pkey PRIMARY KEY (id);


--
-- TOC entry 1920 (class 2606 OID 149896)
-- Name: winning_shares_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY winning_shares
    ADD CONSTRAINT winning_shares_pkey PRIMARY KEY (id);


--
-- TOC entry 1929 (class 0 OID 0)
-- Dependencies: 6
-- Name: public; Type: ACL; Schema: -; Owner: -
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


-- Completed on 2013-08-10 20:30:52

--
-- PostgreSQL database dump complete
--

