import useTaskFiltersStore from "@/hooks/store/useTaskFiltersStore";
import { ActionIcon, Tooltip } from "@mantine/core";
import { IconFilterCancel } from "@tabler/icons-react";

export default function ClearFiltersButton() {
  const clearFilters = useTaskFiltersStore((state) => state.clearFilters);

  return (
    <Tooltip label="Clear filters" openDelay={500} withArrow>
      <ActionIcon variant="filled" size="lg" onClick={() => clearFilters()}>
        <IconFilterCancel
          style={{ width: "60%", height: "60%" }}
          stroke={1.5}
        />
      </ActionIcon>
    </Tooltip>
  );
}
