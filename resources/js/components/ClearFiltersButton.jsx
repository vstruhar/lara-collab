import useTaskFiltersStore from "@/hooks/store/useTaskFiltersStore";
import { ActionIcon, Tooltip } from "@mantine/core";
import { IconX } from "@tabler/icons-react";

export default function ClearFiltersButton() {
  const { hasUrlParams, clearFilters } = useTaskFiltersStore();
  const usingFilters = hasUrlParams(["archived"]);

  return (
    <Tooltip label="Clear filters" openDelay={500} withArrow>
      <ActionIcon
        variant="default"
        size="lg"
        disabled={!usingFilters}
        onClick={() => clearFilters({ keep: ["archived"] })}
      >
        <IconX style={{ width: "40%", height: "40%" }} stroke={2.5} />
      </ActionIcon>
    </Tooltip>
  );
}
