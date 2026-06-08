const noop = () => noopObj;
const noopObj = new Proxy({}, { get: () => noop });
window.Echo = noopObj;
