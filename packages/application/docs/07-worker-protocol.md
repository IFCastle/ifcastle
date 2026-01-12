# Worker RPC Protocol

## Overview

RPC protocol for interaction between workers is designed for making procedure calls 
between different processes, threads, or nodes.

It uses serialization that is not tightly coupled to data types, 
allowing the protocol layer to remain independent of high-level implementations.

`WorkerProtocolArrayTyped` uses JSON or MSGPACK serialization to transmit 
JSON-like data structures.