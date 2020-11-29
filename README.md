### Deployment

- mkdir data/grafana && sudo chown -R 472:472 data/grafana
- mkdir data/grafana-provisioning && sudo chown -R 472:472 data/grafana-provisioning
- mkdir data/prometheus && sudo chown -R 65534:65534 data/prometheus

- docker-compose up -d
- systemctl start hpdb.processors.service && systemctl start hpdb.persistors && systemctl start hpdb.crawlers

### Development

- swagger doc generation: `vendor/bin/openapi -o docs/api.yml Src/Web/Api/V1/`
