import { Pill, useComputedColorScheme } from "@mantine/core";
import { forwardRef } from "react";

export default forwardRef(function TaskGroupLabel(props, ref) {
  const computedColorScheme = useComputedColorScheme();

  return (
    <Pill
      ref={ref}
      size="xs"
      bg={computedColorScheme === "light" ? "blue.6" : "blue.8"}
      fw={600}
      c="white"
      {...props}
    >
      {props.children}
    </Pill>
  );
});
