import { useState } from "react";

export default function useSidebarCollapse(initial = false) {
  const [collapsed, setCollapsed] = useState(initial);
  const toggle = () => setCollapsed((c) => !c);
  return { collapsed, toggle };
}
