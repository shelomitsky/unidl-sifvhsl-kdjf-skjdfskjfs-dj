this.workbox=this.workbox||{},this.workbox.streams=function(e,n,t,r){"use strict";try{self["workbox:streams:6.5.0"]&&_()}catch(e){}function s(e){const r=e.map((e=>Promise.resolve(e).then((e=>function(e){if(e instanceof Response){if(e.body)return e.body.getReader();throw new t.WorkboxError("opaque-streams-source",{type:e.type})}return e instanceof ReadableStream?e.getReader():new Response(e).body.getReader()}(e))))),s=new n.Deferred;let o=0;const a=new ReadableStream({pull(e){return r[o].then((e=>e.read())).then((n=>{if(n.done)return o++,o>=r.length?(e.close(),void s.resolve()):this.pull(e);e.enqueue(n.value)})).catch((e=>{throw s.reject(e),e}))},cancel(){s.resolve()}});return{done:s.promise,stream:a}}function o(e={}){const n=new Headers(e);return n.has("content-type")||n.set("content-type","text/html"),n}function a(e,n){const{done:t,stream:r}=s(e),a=o(n);return{done:t,response:new Response(r,{headers:a})}}function c(){return r.canConstructReadableStream()}return e.concatenate=s,e.concatenateToResponse=a,e.isSupported=c,e.strategy=function(e,n){return async({event:t,request:r,url:s,params:u})=>{const i=e.map((e=>Promise.resolve(e({event:t,request:r,url:s,params:u}))));if(c()){const{done:e,response:r}=a(i,n);return t&&t.waitUntil(e),r}const w=i.map((async e=>{const n=await e;return n instanceof Response?n.blob():new Response(n).blob()})),f=await Promise.all(w),p=o(n);return new Response(new Blob(f),{headers:p})}},e}({},workbox.core._private,workbox.core._private,workbox.core._private);
//# sourceMappingURL=workbox-streams.prod.js.map
