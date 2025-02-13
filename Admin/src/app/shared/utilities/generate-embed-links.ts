export function transformToEmbedLink(link: string): { embedLink: string; status: boolean } {
  // YouTube Links
  const youtubeRegex = /^(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{7,15})(?:[?&][a-zA-Z0-9_-]+=[a-zA-Z0-9_-]+)*$/;
  if (youtubeRegex.test(link)) {
    // @ts-ignore
    const videoId = link?.match(youtubeRegex)[1];
    return {
      status: true,
      embedLink: `https://www.youtube.com/embed/${videoId}`
    };
  }


  // Vimeo Links
  const vimeoRegex = /(?:http|https)?:?\/?\/?(?:www\.)?(?:player\.)?vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|video\/|)(\d+)(?:|\/\?)/;
  if (vimeoRegex.test(link)) {
    // @ts-ignore
    const videoId = link?.match(vimeoRegex)[1];
    return {
      status: true,
      embedLink: `https://player.vimeo.com/video/${videoId}`
    };
  }

  // If the link is not a valid YouTube or Vimeo link, return the original link
  return {
    status: false,
    embedLink: link
  };
}
