# list all entries
`SELECT * FROM 'list';`

# delete entry
`DELETE FROM 'list' WHERE title='Pulp Fiction';`

# correct sqlite_sequence after deleting an entry 
```
SELECT * FROM sqlite_sequence;
UPDATE SQLITE_SEQUENCE SET SEQ=230 WHERE NAME='list';
```