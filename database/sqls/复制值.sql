update table_terms set table_terms.description = (select table_explorations.topic_type from table_explorations where table_explorations.name = table_terms.term);


update table_terms_suggestions set table_terms_suggestions.suggestion_json = (select table_explorations.suggestion_json from table_explorations where table_explorations.name = table_terms_suggestions.term order by table_explorations.name desc limit 1) where table_terms_suggestions.suggestion_json is not null;

update table_terms_suggestions set table_terms_suggestions.json_suggestion = (select table_explorations.json_suggestion from table_explorations where table_explorations.name = table_terms_suggestions.term order by table_explorations.name desc limit 1) where table_terms_suggestions.json_suggestion is null;
