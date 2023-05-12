project data for reference only. doesnt contain uses, namespaces, bind logic and php-docs

1. transaction-logic: is part of transaction processing
2. worker-stat: is a part of worker to benchmark redis incoming rps. can be checked via following command using apache bench
3. cs fixer example

```
$ ab -k -t 10 -c 10 http://localhost/to/path
```