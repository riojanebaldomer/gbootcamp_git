/**
 * Interfaces
 */
export interface IPluginList {
    plugins: PluginLists[];
}

export interface ICreateSymlinkParam {
    target: string;
    link: string;
}

export interface ISymlink {
    symlinks: SymlinkType[];
}

export interface IUntrackedSymlinks {
    untracked: UntrackedSymlinkType[];
}

export type UntrackedSymlinkType = {
    id: React.Key;
    target?: string;
    link?: string;
    description?: string;
}

/**
 * Types
 */
export type PluginLists = {
    plugin_name: string;
    is_active: boolean;
    is_installed: boolean;
    img_src: string;
}

export type SymlinkType = {
    id: React.Key;
    target?: string;
    link?: string;
    status?: string;
    description?: string;
}