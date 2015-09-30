#!/bin/bash

SVR_USERNAME=user

cat keys/id_rsa.pub | ssh $SVR_USERNAME@imdc.ca "mkdir -p ~/.ssh/ ; cat - >> ~/.ssh/authorized_keys ; chmod 600 ~/.ssh/authorized_keys"
