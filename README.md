### grin-miner-ethos
grin-miner ethOS Mining OS integration (ethosdistro.com)

Works with ethos 1.3.3 (latest), other versions are not tested.

grin-miner version: 1.0.0-cuda-ocl-ethos

SSL disabled in grin-miner.stub.conf, uncomment the line "stratum_server_tls_enabled = true" to enable SSL.

```
miner [worker] grin-miner
grin-miner=proxywallet farm@test.com
grin-miner=proxypool1 us-east.stratum.grinmint.com:3416
grin-miner=poolpass1 farmpw
```

Initial port by amnesium [https://github.com/amnesium/grin-miner-ethos]
