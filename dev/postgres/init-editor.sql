
create function update_updated_at_column()
    returns trigger
    language plpgsql
as
$$
begin
    IF NEW.updated_at IS DISTINCT FROM OLD.updated_at THEN
        -- If the updated_at value is being changed or is null, set it to the current time.
        NEW.updated_at = NOW();
    END IF;
    RETURN NEW;
end;
$$;

alter function update_updated_at_column() owner to template;


CREATE TABLE public.sessions (
   id uuid DEFAULT gen_random_uuid() NOT NULL,
   user_id uuid NOT NULL,
   user_agent character varying(510) NOT NULL,
   user_ip character varying(16) NOT NULL,
   created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
   updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
   tag character varying,
   iv character varying
);

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);

ALTER TABLE public.sessions OWNER TO template;

CREATE INDEX idx_sessions_id ON public.users USING btree (id);

CREATE TRIGGER update_sessions_updated_at BEFORE UPDATE ON public.sessions FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


CREATE TABLE public.users (
 id uuid DEFAULT gen_random_uuid() NOT NULL,
 username character varying(100) NOT NULL,
 email character varying(255) NOT NULL,
 password_hash character varying(60) NOT NULL,
 created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
 updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.users OWNER TO template;

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);

CREATE INDEX idx_users_email ON public.users USING btree (email);


CREATE INDEX idx_users_id ON public.users USING btree (id);

CREATE INDEX idx_users_password_hash ON public.users USING btree (password_hash);

CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON public.users FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();

